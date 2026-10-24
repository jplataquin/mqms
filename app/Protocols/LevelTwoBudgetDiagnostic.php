<?php

namespace App\Protocols;

use App\Models\Project;
use App\Models\ContractItem;
use App\Models\MaterialQuantityRequestItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;

class LevelTwoBudgetDiagnostic implements ProtocolInterface
{
    /**
     * Protocol Name.
     */
    public const NAME = 'Level 2 Budget Diagnostic';

    /**
     * Protocol Slug.
     */
    public const SLUG = 'level-2-budget-diagnostic';

    /**
     * Protocol Description.
     */
    public const DESCRIPTION = 'Verifies that material quantities in approved purchase orders do not exceed approved material quantity requests, and that total approved purchase order amounts per contract item do not exceed the contract item amount.';

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
     * - contract_item_id (int, optional): Filter checks to a specific contract item.
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

        $targetContractItemId = $params['contract_item_id'] ?? null;

        // =========================================================================
        // PART 1: MQR Items vs PO Items Quantity Verification
        // =========================================================================
        $mqrItemsQuery = MaterialQuantityRequestItem::query()
            ->whereHas('Request', function ($q) use ($projectId, $targetContractItemId) {
                $q->whereNull('deleted_at')
                  ->where('project_id', $projectId)
                  ->whereIn('status', ['APRV', 'CLSD']);

                if ($targetContractItemId) {
                    $q->where('contract_item_id', $targetContractItemId);
                }
            })
            ->whereIn('status', ['APRV', 'CLSD'])
            ->with(['Request.ContractItem', 'Request.Component', 'ComponentItem', 'MaterialItem']);

        $mqrItems = $mqrItemsQuery->get();

        $quantityViolations = [];

        foreach ($mqrItems as $mqri) {
            $allowedQuantity = (float) $mqri->requested_quantity;

            // Find all approved PO items referencing this MQR item
            $poItems = PurchaseOrderItem::query()
                ->where('material_quantity_request_item_id', $mqri->id)
                ->whereHas('PurchaseOrder', function ($q) {
                    $q->whereNull('deleted_at')
                      ->where('status', 'APRV');
                })
                ->with('PurchaseOrder')
                ->get();

            $totalPoQuantity = (float) $poItems->sum('quantity');
            $variance = $totalPoQuantity - $allowedQuantity;

            if ($variance > 0.00001) {
                $mqr = $mqri->Request;
                $contractItem = $mqr ? $mqr->ContractItem : null;
                $component = $mqr ? $mqr->Component : null;
                $materialItem = $mqri->MaterialItem;

                $poBreakdown = [];
                foreach ($poItems as $poi) {
                    $poBreakdown[] = [
                        'purchase_order_item_id' => $poi->id,
                        'purchase_order_id'      => $poi->purchase_order_id,
                        'purchase_order_url'     => "/purchase_order/{$poi->purchase_order_id}",
                        'quantity'               => (float) $poi->quantity,
                        'price'                  => (float) $poi->price,
                        'amount'                 => round((float) $poi->quantity * (float) $poi->price, 2),
                        'status'                 => $poi->PurchaseOrder ? $poi->PurchaseOrder->status : 'N/A',
                    ];
                }

                $quantityViolations[] = [
                    'type'                             => 'mqr_quantity_exceeded',
                    'material_quantity_request_item_id'=> $mqri->id,
                    'material_quantity_request_id'     => $mqri->material_quantity_request_id,
                    'material_quantity_request_url'    => "/material_quantity_request/{$mqri->material_quantity_request_id}",
                    'material_item_id'                 => $mqri->material_item_id,
                    'material_name'                    => $materialItem ? $materialItem->formatted_name : "Material Item #{$mqri->material_item_id}",
                    'contract_item_id'                 => $contractItem ? $contractItem->id : null,
                    'contract_item_name'               => $contractItem ? $contractItem->name : null,
                    'component_id'                     => $component ? $component->id : null,
                    'component_name'                   => $component ? $component->name : null,
                    'allowed_mqr_quantity'             => $allowedQuantity,
                    'total_po_quantity'                => round($totalPoQuantity, 4),
                    'over_ordered_quantity'            => round($variance, 4),
                    'purchase_orders_count'            => $poItems->count(),
                    'po_breakdown'                     => $poBreakdown,
                ];
            }
        }

        // =========================================================================
        // PART 2: Contract Item PO Total Amount vs Contract Amount
        // =========================================================================
        $contractItemsQuery = ContractItem::query()
            ->whereNull('deleted_at')
            ->whereHas('Section', function ($q) use ($projectId) {
                $q->whereNull('deleted_at')
                  ->where('project_id', $projectId);
            })
            ->with(['Components' => function ($q) {
                $q->whereNull('deleted_at');
            }]);

        if ($targetContractItemId) {
            $contractItemsQuery->where('id', $targetContractItemId);
        }

        $contractItems = $contractItemsQuery->get();

        $amountViolations = [];
        $contractItemsSummary = [];

        foreach ($contractItems as $ci) {
            $contractAmount = (float) $ci->contract_amount;
            $componentIds = $ci->Components->pluck('id')->filter()->all();

            // Find all approved POs belonging to this contract item
            $approvedPos = PurchaseOrder::query()
                ->whereNull('deleted_at')
                ->where('status', 'APRV')
                ->where(function ($q) use ($ci, $componentIds) {
                    $q->where('contract_item_id', $ci->id);
                    if (!empty($componentIds)) {
                        $q->orWhereIn('component_id', $componentIds);
                    }
                })
                ->with('Items')
                ->get();

            $totalPoAmount = 0.0;
            $poList = [];

            foreach ($approvedPos as $po) {
                $poTotal = (float) $po->getGrandTotal();
                $totalPoAmount += $poTotal;

                $poList[] = [
                    'purchase_order_id'  => $po->id,
                    'purchase_order_url' => "/purchase_order/{$po->id}",
                    'component_id'       => $po->component_id,
                    'supplier_id'        => $po->supplier_id,
                    'amount'             => round($poTotal, 2),
                ];
            }

            $percentageUsed = $contractAmount > 0 ? ($totalPoAmount / $contractAmount) * 100 : 0.0;
            $variance = $totalPoAmount - $contractAmount;
            $isOverBudget = ($contractAmount > 0 && $variance > 0.00001);

            $ciData = [
                'contract_item_id'     => $ci->id,
                'contract_item_code'   => $ci->item_code,
                'contract_item_name'   => $ci->name,
                'contract_amount'      => round($contractAmount, 2),
                'total_po_amount'      => round($totalPoAmount, 2),
                'percentage_used'      => round($percentageUsed, 2),
                'over_budget_amount'   => max(0.0, round($variance, 2)),
                'purchase_orders_count'=> $approvedPos->count(),
                'is_over_budget'       => $isOverBudget,
                'purchase_orders'      => $poList,
            ];

            $contractItemsSummary[] = $ciData;

            if ($isOverBudget) {
                $amountViolations[] = array_merge(['type' => 'contract_amount_exceeded'], $ciData);
            }
        }

        $allViolations = array_merge($quantityViolations, $amountViolations);
        $passed = count($allViolations) === 0;

        $summaryParts = [];
        if ($passed) {
            $summaryParts[] = "All approved Purchase Orders are within MQR quantities and Contract Item amounts for Project '{$project->name}'.";
        } else {
            if (count($quantityViolations) > 0) {
                $summaryParts[] = count($quantityViolations) . " MQR item(s) exceeded by PO quantities.";
            }
            if (count($amountViolations) > 0) {
                $summaryParts[] = count($amountViolations) . " Contract Item(s) exceeded by PO total amounts.";
            }
        }

        return [
            'protocol'                  => self::NAME,
            'slug'                      => self::SLUG,
            'status'                    => 'success',
            'passed'                    => $passed,
            'project_id'                => $project->id,
            'project_name'              => $project->name,
            'total_mqr_items_checked'   => $mqrItems->count(),
            'total_contract_items'      => $contractItems->count(),
            'violations_count'          => count($allViolations),
            'quantity_violations_count' => count($quantityViolations),
            'amount_violations_count'   => count($amountViolations),
            'violations'                => $allViolations,
            'quantity_violations'       => $quantityViolations,
            'amount_violations'         => $amountViolations,
            'contract_items_summary'    => $contractItemsSummary,
            'summary'                   => implode(' ', $summaryParts),
        ];
    }
}
