<?php

namespace App\Protocols;

use App\Models\Project;
use App\Models\ComponentItem;
use App\Models\MaterialQuantityRequestItem;
use App\Models\MaterialQuantityRequest;
use App\Models\MaterialQuantity;
use App\Models\MaterialItem;
use Illuminate\Support\Facades\DB;

class LevelOneBudgetDiagnostic implements ProtocolInterface
{
    /**
     * Protocol Name.
     */
    public const NAME = 'Level 1 Budget Diagnostic';

    /**
     * Protocol Slug.
     */
    public const SLUG = 'level-1-budget-diagnostic';

    /**
     * Protocol Description.
     */
    public const DESCRIPTION = 'Verifies that total requested quantity (scaled by equivalent) in approved material quantity requests does not exceed component item budget.';

    /**
     * Helper static execution method.
     *
     * @param int|Project $project
     * @param array $options
     * @return array
     */
    public static function check($project, array $options = []): array
    {
        $projectId = $project instanceof Project ? $project->id : (int) $project;
        return (new static())->run(array_merge(['project_id' => $projectId], $options));
    }

    /**
     * Run the protocol with the given parameters.
     *
     * Supported parameters:
     * - project_id (int, required): The ID of the project to check.
     * - component_id (int, optional): Filter checks to a specific component.
     * - component_item_id (int, optional): Filter checks to a specific component item.
     * - include_closed (bool, optional): Include closed requests (CLSD) alongside APRV. Default: true.
     * - only_approved_components (bool, optional): Check only components with status 'APRV'. Default: false.
     *
     * @param array $params
     * @return array
     */
    public function run(array $params = []): array
    {
        $projectId = $params['project_id'] ?? null;
        if (!$projectId) {
            return [
                'protocol' => self::NAME,
                'status'   => 'error',
                'passed'   => false,
                'message'  => 'Project ID is required.',
                'data'     => [],
            ];
        }

        $project = Project::find($projectId);
        if (!$project) {
            return [
                'protocol' => self::NAME,
                'status'   => 'error',
                'passed'   => false,
                'message'  => "Project with ID {$projectId} not found.",
                'data'     => [],
            ];
        }

        $targetComponentId = $params['component_id'] ?? null;
        $targetComponentItemId = $params['component_item_id'] ?? null;
        $includeClosed = $params['include_closed'] ?? true;
        $onlyApprovedComponents = $params['only_approved_components'] ?? false;

        $statuses = ['APRV'];
        if ($includeClosed) {
            $statuses[] = 'CLSD';
        }

        // Retrieve component items belonging to the project
        $componentItemsQuery = ComponentItem::query()
            ->whereNull('deleted_at')
            ->whereHas('Component', function ($q) use ($projectId, $targetComponentId, $onlyApprovedComponents) {
                $q->whereNull('deleted_at')
                  ->whereHas('Section', function ($sq) use ($projectId) {
                      $sq->where('project_id', $projectId)
                         ->whereNull('deleted_at');
                  });

                if ($targetComponentId) {
                    $q->where('id', $targetComponentId);
                }

                if ($onlyApprovedComponents) {
                    $q->where('status', 'APRV');
                }
            })
            ->with(['Component.Section', 'Component.ContractItem']);

        if ($targetComponentItemId) {
            $componentItemsQuery->where('id', $targetComponentItemId);
        }

        $componentItems = $componentItemsQuery->get();

        $violations = [];
        $totalItems = $componentItems->count();

        foreach ($componentItems as $componentItem) {
            $allowedBudget = (float) $componentItem->quantity;

            // Fetch approved/closed MQR items for this component item
            // Ensure the parent material_quantity_request is not deleted
            $mqrItems = MaterialQuantityRequestItem::where('component_item_id', $componentItem->id)
                ->whereIn('status', $statuses)
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('material_quantity_requests')
                        ->whereColumn('material_quantity_requests.id', 'material_quantity_request_items.material_quantity_request_id')
                        ->whereNull('material_quantity_requests.deleted_at');
                })
                ->get();

            $totalActualQuantity = 0.0;
            $breakdown = [];

            // Pre-load material quantities for this component item
            $materialQuantities = MaterialQuantity::where('component_item_id', $componentItem->id)
                ->whereNull('deleted_at')
                ->get()
                ->keyBy('material_item_id');

            // Pre-load parent MaterialQuantityRequests and MaterialItems for traceability
            $requestIds = $mqrItems->pluck('material_quantity_request_id')->unique()->filter();
            $requests = MaterialQuantityRequest::whereIn('id', $requestIds)->get()->keyBy('id');

            $materialItemIds = $mqrItems->pluck('material_item_id')->unique()->filter();
            $materialItems = MaterialItem::whereIn('id', $materialItemIds)->get()->keyBy('id');

            foreach ($mqrItems as $mqri) {
                $materialQuantity = $materialQuantities->get($mqri->material_item_id);
                $equivalent = $materialQuantity ? (float) $materialQuantity->equivalent : 1.0;
                $actualQty = (float) $mqri->requested_quantity * $equivalent;

                $totalActualQuantity += $actualQty;

                $parentReq = $requests->get($mqri->material_quantity_request_id);
                $matItem = $materialItems->get($mqri->material_item_id);

                $breakdown[] = [
                    'material_quantity_request_item_id' => $mqri->id,
                    'material_quantity_request_id'      => $mqri->material_quantity_request_id,
                    'material_quantity_request_url'     => "/material_quantity_request/{$mqri->material_quantity_request_id}",
                    'request_date_needed'               => $parentReq ? $parentReq->date_needed : null,
                    'request_status'                    => $parentReq ? $parentReq->status : $mqri->status,
                    'material_item_id'                  => $mqri->material_item_id,
                    'material_item_name'                => $matItem ? $matItem->formatted_name : "Material Item #{$mqri->material_item_id}",
                    'requested_quantity'                => (float) $mqri->requested_quantity,
                    'equivalent'                        => $equivalent,
                    'actual_quantity'                   => round($actualQty, 4),
                    'status'                            => $mqri->status,
                ];
            }

            $variance = $totalActualQuantity - $allowedBudget;

            // Flag as violation if total actual requested exceeds allowed budget
            if ($variance > 0.00001) {
                $component = $componentItem->Component;
                $contractItem = $component ? $component->ContractItem : null;
                $contractItemCode = $contractItem ? $contractItem->item_code : null;
                $contractItemDesc = $contractItem ? $contractItem->description : null;
                $contractItemType = $contractItem ? $contractItem->item_type : null;
                $contractItemId   = $contractItem ? $contractItem->id : ($component ? $component->contract_item_id : null);

                $contractItemTypeName = match($contractItemType) {
                    'MATR' => 'Material',
                    'NMAT' => 'Non-Material',
                    'OPEX' => 'Operating Expense',
                    default => $contractItemType ?? 'N/A',
                };

                $violations[] = [
                    'component_item_id'         => $componentItem->id,
                    'component_item_name'       => $componentItem->name,
                    'component_id'              => $componentItem->component_id,
                    'component_name'            => $component ? $component->name : null,
                    'component_status'          => $component ? $component->status : null,
                    'component_url'             => $componentItem->component_id ? "/project/section/contract_item/component/{$componentItem->component_id}" : null,
                    'contract_item_id'          => $contractItemId,
                    'contract_item_code'        => $contractItemCode,
                    'contract_item_description' => $contractItemDesc,
                    'contract_item_name'        => $contractItem ? $contractItem->name : null,
                    'contract_item_type'        => $contractItemType,
                    'contract_item_type_name'   => $contractItemTypeName,
                    'contract_item_url'         => $contractItemId ? "/project/section/contract_item/{$contractItemId}" : null,
                    'record_type'               => 'Component Item',
                    'section_id'                => $component ? $component->section_id : null,
                    'allowed_budget'            => $allowedBudget,
                    'total_requested'           => round($totalActualQuantity, 4),
                    'over_budget_quantity'      => round($variance, 4),
                    'unit_id'                   => $componentItem->unit_id,
                    'unit_text'                 => $componentItem->unit_text,
                    'items_count'               => $mqrItems->count(),
                    'breakdown'                 => $breakdown,
                ];
            }
        }

        $passed = count($violations) === 0;

        return [
            'protocol'         => self::NAME,
            'slug'             => self::SLUG,
            'status'           => 'success',
            'passed'           => $passed,
            'project_id'       => $project->id,
            'project_name'     => $project->name,
            'total_items'      => $totalItems,
            'violations_count' => count($violations),
            'violations'       => $violations,
            'summary'          => $passed
                ? "All {$totalItems} component item(s) in Project '{$project->name}' are within budget."
                : "Found " . count($violations) . " component item(s) exceeding budget in Project '{$project->name}'.",
        ];
    }
}
