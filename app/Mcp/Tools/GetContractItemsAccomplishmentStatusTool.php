<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\ContractItem;
use Carbon\Carbon;
use Generator;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('Get Contract Items Accomplishment Status')]
class GetContractItemsAccomplishmentStatusTool extends Tool
{
    public function name(): string
    {
        return 'get_contract_items_accomplishment_status';
    }

    public function description(): string
    {
        return 'Retrieves the accomplishment status of contract items (delayed, on-track, or ahead) based on actual vs target accomplishment, along with current actual quantities, target quantities, and percentage completion. Allows filtering by project_id, section_id, as_of_date, and status.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        $schema->integer('project_id')
            ->description('Optional project ID to filter contract items by.')
            ->optional();

        $schema->integer('section_id')
            ->description('Optional section ID to filter contract items by.')
            ->optional();

        $schema->string('as_of_date')
            ->description('Optional evaluation cutoff date (YYYY-MM-DD). Defaults to the current date.')
            ->optional();

        $schema->string('status')
            ->description('Optional status filter: delayed, on-track, or ahead.')
            ->optional();

        $schema->boolean('include_excluded')
            ->description('Optional flag to include contract items flagged as excluded from monitoring. Defaults to false.')
            ->optional();

        return $schema;
    }

    public function handle(array $arguments): ToolResult|Generator
    {
        $projectId = isset($arguments['project_id']) && $arguments['project_id'] !== '' ? (int) $arguments['project_id'] : null;
        $sectionId = isset($arguments['section_id']) && $arguments['section_id'] !== '' ? (int) $arguments['section_id'] : null;
        $asOfDate = ! empty($arguments['as_of_date']) ? trim((string) $arguments['as_of_date']) : Carbon::today()->format('Y-m-d');
        $statusFilter = ! empty($arguments['status']) ? strtolower(trim((string) $arguments['status'])) : null;
        $includeExcluded = filter_var($arguments['include_excluded'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $query = ContractItem::query();

        if (! is_null($sectionId)) {
            $query->where('section_id', $sectionId);
        }

        if (! is_null($projectId)) {
            $query->whereHas('Section', function ($q) use ($projectId) {
                $q->where('project_id', $projectId);
            });
        }

        if (! $includeExcluded) {
            $query->where(function ($q) {
                $q->where('exclude_from_monitoring', '!=', 1)
                  ->orWhereNull('exclude_from_monitoring');
            });
        }

        $query->with([
            'Section.Project',
            'Accomplishments' => function ($q) use ($asOfDate) {
                $q->where(function ($sub) use ($asOfDate) {
                    $sub->where('entry_data', '<=', $asOfDate)
                        ->orWhere(function ($s2) use ($asOfDate) {
                            $s2->whereNull('entry_data')
                               ->where('created_at', '<=', $asOfDate . ' 23:59:59');
                        });
                })->orderBy('entry_data', 'desc')
                  ->orderBy('created_at', 'desc')
                  ->orderBy('id', 'desc');
            },
        ]);

        $contractItems = $query->orderBy('id', 'asc')->get();

        $items = [];
        $delayedCount = 0;
        $onTrackCount = 0;
        $aheadCount = 0;

        foreach ($contractItems as $item) {
            $actuals = $item->Accomplishments->where('type', 'ACTUAL');
            $targets = $item->Accomplishments->where('type', 'TARGET');

            $latestActual = $actuals->first();
            $latestTarget = $targets->first();

            $actualQty = $latestActual ? (float) $latestActual->quantity : 0.0;
            $targetQty = $latestTarget ? (float) $latestTarget->quantity : 0.0;
            $scopeQty = (float) $item->contract_quantity;

            // Status determination
            if ($actualQty < $targetQty) {
                $itemStatus = 'delayed';
            } elseif ($actualQty > $targetQty) {
                $itemStatus = 'ahead';
            } else {
                $itemStatus = 'on-track';
            }

            if ($itemStatus === 'delayed') {
                $delayedCount++;
            } elseif ($itemStatus === 'ahead') {
                $aheadCount++;
            } else {
                $onTrackCount++;
            }

            // Apply status filter if supplied
            if ($statusFilter && $itemStatus !== $statusFilter) {
                continue;
            }

            $percentActualOfScope = $scopeQty > 0 ? round(($actualQty / $scopeQty) * 100, 2) : 0.0;
            $percentTargetOfScope = $scopeQty > 0 ? round(($targetQty / $scopeQty) * 100, 2) : 0.0;

            if ($targetQty > 0) {
                $percentActualVsTarget = round(($actualQty / $targetQty) * 100, 2);
            } elseif ($actualQty > 0) {
                $percentActualVsTarget = 100.0;
            } else {
                $percentActualVsTarget = 0.0;
            }

            $actualDateStr = null;
            if ($latestActual) {
                if ($latestActual->entry_data instanceof \DateTimeInterface) {
                    $actualDateStr = $latestActual->entry_data->format('Y-m-d');
                } elseif ($latestActual->entry_data) {
                    $actualDateStr = substr((string) $latestActual->entry_data, 0, 10);
                } elseif ($latestActual->created_at instanceof \DateTimeInterface) {
                    $actualDateStr = $latestActual->created_at->format('Y-m-d');
                }
            }

            $targetDateStr = null;
            if ($latestTarget) {
                if ($latestTarget->entry_data instanceof \DateTimeInterface) {
                    $targetDateStr = $latestTarget->entry_data->format('Y-m-d');
                } elseif ($latestTarget->entry_data) {
                    $targetDateStr = substr((string) $latestTarget->entry_data, 0, 10);
                } elseif ($latestTarget->created_at instanceof \DateTimeInterface) {
                    $targetDateStr = $latestTarget->created_at->format('Y-m-d');
                }
            }

            $items[] = [
                'contract_item_id' => $item->id,
                'item_code' => $item->item_code,
                'description' => $item->description,
                'section_id' => $item->section_id,
                'section_name' => $item->Section?->name,
                'project_id' => $item->Section?->project_id,
                'project_name' => $item->Section?->Project?->name,
                'contract_quantity' => $scopeQty,
                'contract_unit' => $item->contract_unit_text,
                'status' => $itemStatus,
                'actual_accomplishment' => [
                    'quantity' => $actualQty,
                    'percentage_of_scope' => $percentActualOfScope,
                    'date' => $actualDateStr,
                ],
                'target_accomplishment' => [
                    'quantity' => $targetQty,
                    'percentage_of_scope' => $percentTargetOfScope,
                    'date' => $targetDateStr,
                ],
                'percent_actual_vs_target' => $percentActualVsTarget,
                'variance_quantity' => round($actualQty - $targetQty, 4),
                'variance_percentage' => round($percentActualOfScope - $percentTargetOfScope, 2),
            ];
        }

        return ToolResult::json([
            'as_of_date' => $asOfDate,
            'filters_applied' => array_filter([
                'project_id' => $projectId,
                'section_id' => $sectionId,
                'as_of_date' => $asOfDate,
                'status' => $statusFilter,
                'include_excluded' => $includeExcluded ? true : null,
            ], fn ($v) => ! is_null($v)),
            'summary' => [
                'total_contract_items' => count($contractItems),
                'filtered_contract_items' => count($items),
                'delayed' => $delayedCount,
                'on_track' => $onTrackCount,
                'ahead' => $aheadCount,
            ],
            'records' => $items,
        ]);
    }
}
