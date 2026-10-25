<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\MaterialItem;
use App\Models\MaterialQuantityRequest;
use App\Models\MaterialQuantityRequestItem;
use App\Models\Project;
use Carbon\Carbon;
use Generator;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('Get Material Need Schedule')]
class GetMaterialNeedScheduleTool extends Tool
{
    public function name(): string
    {
        return 'get_material_need_schedule';
    }

    public function description(): string
    {
        return 'Retrieves the schedule of materials needed for active projects based on approved Material Quantity Requests (Material Objectives) within a date range, grouped by project and date needed.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        $schema->integer('project_id')
            ->description('Optional project ID to filter the schedule by.');

        $schema->string('date_from')
            ->description('Optional start date (YYYY-MM-DD). Defaults to the current date.');

        $schema->string('date_to')
            ->description('Optional end date (YYYY-MM-DD). Defaults to 5 days from the start date.');

        return $schema;
    }

    public function handle(array $arguments): ToolResult|Generator
    {
        $projectId = isset($arguments['project_id']) && $arguments['project_id'] !== ''
            ? (int) $arguments['project_id']
            : null;

        $dateFromStr = ! empty($arguments['date_from']) ? trim((string) $arguments['date_from']) : null;
        $dateToStr = ! empty($arguments['date_to']) ? trim((string) $arguments['date_to']) : null;

        try {
            $from = $dateFromStr ? Carbon::parse($dateFromStr)->startOfDay() : Carbon::now()->startOfDay();
        } catch (\Exception $e) {
            return ToolResult::error("Invalid date_from format: {$dateFromStr}. Expected YYYY-MM-DD.");
        }

        try {
            $to = $dateToStr ? Carbon::parse($dateToStr)->endOfDay() : (clone $from)->addDays(5)->endOfDay();
        } catch (\Exception $e) {
            return ToolResult::error("Invalid date_to format: {$dateToStr}. Expected YYYY-MM-DD.");
        }

        $query = MaterialQuantityRequest::query()
            ->where('status', 'APRV')
            ->whereNotNull('date_needed')
            ->where('date_needed', '>=', $from->format('Y-m-d'))
            ->where('date_needed', '<=', $to->format('Y-m-d'))
            ->orderBy('date_needed', 'ASC');

        if ($projectId) {
            $project = Project::find($projectId);
            if (! $project) {
                return ToolResult::error("Project with ID {$projectId} not found.");
            }
            if ($project->status !== 'ACTV') {
                return ToolResult::error("Project with ID {$projectId} is not active.");
            }
            $query->where('project_id', $projectId);
        } else {
            // Only include active projects
            $activeProjectIds = Project::where('status', 'ACTV')->pluck('id')->all();
            $query->whereIn('project_id', $activeProjectIds);
        }

        $requests = $query->get();

        // Group by project_id -> raw date_needed -> requests
        $projectsMap = [];
        $materialItemCache = [];

        foreach ($requests as $mr) {
            $pId = $mr->project_id;
            $rawDate = $mr->getRawOriginal('date_needed');
            $formattedDate = (new Carbon($rawDate))->format('Y-m-d');

            if (! isset($projectsMap[$pId])) {
                $proj = Project::find($pId);
                $projectsMap[$pId] = [
                    'project_id' => $pId,
                    'project_name' => $proj ? $proj->name : 'Unknown Project',
                    'dates' => [],
                ];
            }

            if (! isset($projectsMap[$pId]['dates'][$formattedDate])) {
                $projectsMap[$pId]['dates'][$formattedDate] = [
                    'date_needed' => $formattedDate,
                    'requests' => [],
                ];
            }

            $items = MaterialQuantityRequestItem::where('material_quantity_request_id', $mr->id)->get();
            $itemDetails = [];

            foreach ($items as $item) {
                if (! isset($materialItemCache[$item->material_item_id])) {
                    $materialItemCache[$item->material_item_id] = MaterialItem::find($item->material_item_id);
                }
                $mItem = $materialItemCache[$item->material_item_id];

                $itemName = $mItem ? ($mItem->formatted_name ?? $mItem->name) : 'Unknown Material';
                $itemDetails[] = [
                    'material_quantity_request_item_id' => $item->id,
                    'material_item_id' => $item->material_item_id,
                    'material_name' => $itemName,
                    'requested_quantity' => (float) $item->requested_quantity,
                    'summary' => $itemName . ' x ' . number_format((float) $item->requested_quantity, 2),
                ];
            }

            $projectsMap[$pId]['dates'][$formattedDate]['requests'][] = [
                'material_quantity_request_id' => $mr->id,
                'status' => $mr->status,
                'items' => $itemDetails,
            ];
        }

        // Re-structure to a clean list-based format
        $result = [];
        foreach ($projectsMap as $pData) {
            $datesList = array_values($pData['dates']);
            $result[] = [
                'project_id' => $pData['project_id'],
                'project_name' => $pData['project_name'],
                'schedule' => $datesList,
            ];
        }

        return ToolResult::json([
            'filters_applied' => [
                'project_id' => $projectId,
                'date_from' => $from->format('Y-m-d'),
                'date_to' => $to->format('Y-m-d'),
            ],
            'total_projects' => count($result),
            'projects' => $result,
        ]);
    }
}
