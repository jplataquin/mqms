<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Project;
use Carbon\Carbon;
use Generator;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('Overall Project Accomplishment')]
class GetOverallProjectAccomplishmentTool extends Tool
{
    public function name(): string
    {
        return 'overall_project_accomplishment';
    }

    public function description(): string
    {
        return 'Calculates overall project progress, accomplishments, slippage, and velocity metrics matching the project accomplishment studio overall progress view. Supports filtering by project id and optional as_of_date.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        $schema->integer('id')
            ->description('The primary key ID of the project.')
            ->optional();

        $schema->integer('project_id')
            ->description('Optional alias for id. The primary key ID of the project.')
            ->optional();

        $schema->string('as_of_date')
            ->description('Optional cutoff date (YYYY-MM-DD). If omitted, defaults to the latest accomplishment progress report date or current date.')
            ->optional();

        return $schema;
    }

    public function handle(array $arguments): ToolResult|Generator
    {
        $projectId = isset($arguments['id']) && $arguments['id'] !== ''
            ? (int) $arguments['id']
            : (isset($arguments['project_id']) && $arguments['project_id'] !== '' ? (int) $arguments['project_id'] : null);

        if (! $projectId) {
            return ToolResult::error('Project ID is required. Please provide "id" or "project_id".');
        }

        $project = Project::find($projectId);
        if (! $project) {
            return ToolResult::error("Project with ID {$projectId} not found.");
        }

        $project->load([
            'Sections' => function ($q) {
                $q->whereNull('deleted_at')->orderBy('name', 'asc');
            },
            'Sections.ContractItems' => function ($q) {
                $q->whereNull('deleted_at')
                    ->where(function ($sq) {
                        $sq->where('exclude_from_monitoring', '!=', 1)
                            ->orWhereNull('exclude_from_monitoring');
                    })
                    ->orderBy('item_code', 'asc');
            },
            'Sections.ContractItems.Accomplishments' => function ($q) {
                $q->orderBy('entry_data', 'asc');
            },
        ]);

        // First pass: collect all dates and calculate total monitored contract amount
        $projectFirstActualDate = null;
        $projectLatestActualDate = null;
        $projectLatestTargetDate = null;
        $projectAllTargetDates = [];
        $totalContractValue = 0.0;
        $monitoredItemsCount = 0;

        foreach ($project->Sections as $section) {
            if (method_exists($section, 'trashed') && $section->trashed()) {
                continue;
            }

            foreach ($section->ContractItems as $ci) {
                if (method_exists($ci, 'trashed') && $ci->trashed()) {
                    continue;
                }
                if ((int) $ci->exclude_from_monitoring === 1) {
                    continue;
                }

                $monitoredItemsCount++;
                $scope = (float) $ci->contract_quantity;
                $price = (float) $ci->contract_unit_price;
                $totalContractValue += ($scope * $price);

                if ($ci->Accomplishments) {
                    foreach ($ci->Accomplishments as $acc) {
                        if (! $acc->entry_data) {
                            continue;
                        }
                        $accDateStr = Carbon::parse($acc->entry_data)->format('Y-m-d');

                        if ($acc->type === 'ACTUAL') {
                            if (! $projectFirstActualDate || $accDateStr < $projectFirstActualDate) {
                                $projectFirstActualDate = $accDateStr;
                            }
                            if (! $projectLatestActualDate || $accDateStr > $projectLatestActualDate) {
                                $projectLatestActualDate = $accDateStr;
                            }
                        }

                        if ($acc->type === 'TARGET') {
                            $projectAllTargetDates[$accDateStr] = true;
                            if (! $projectLatestTargetDate || $accDateStr > $projectLatestTargetDate) {
                                $projectLatestTargetDate = $accDateStr;
                            }
                        }
                    }
                }
            }
        }

        // Determine effective as_of_date
        $userAsOfDate = isset($arguments['as_of_date']) && trim((string) $arguments['as_of_date']) !== ''
            ? trim((string) $arguments['as_of_date'])
            : null;

        $isDateInferred = false;
        if ($userAsOfDate) {
            try {
                $asOfDate = Carbon::parse($userAsOfDate)->format('Y-m-d');
            } catch (\Throwable $e) {
                return ToolResult::error("Invalid as_of_date format '{$userAsOfDate}'. Expected format: YYYY-MM-DD.");
            }
        } else {
            $isDateInferred = true;
            $asOfDate = $projectLatestActualDate ?? $projectLatestTargetDate ?? Carbon::today()->format('Y-m-d');
        }

        // Find next nearest project target date relative to as_of_date
        $sortedTargetDates = array_keys($projectAllTargetDates);
        sort($sortedTargetDates);

        $nextNearestProjectTargetDate = null;
        foreach ($sortedTargetDates as $tDate) {
            if ($tDate >= $asOfDate) {
                $nextNearestProjectTargetDate = $tDate;
                break;
            }
        }

        $nextTargetDate = $nextNearestProjectTargetDate ?? $projectLatestTargetDate;
        $targetDateToUse = $projectLatestTargetDate;

        // Second pass: compute actual and target accomplishments as of as_of_date
        $sumWeightedActual = 0.0;
        $sumWeightedTarget = 0.0;
        $sumNextWeightedTarget = 0.0;
        $totalAccomplishedAmount = 0.0;
        $totalTargetAmount = 0.0;
        $actualLatestDateAsOf = null;
        $itemsBreakdown = [];

        foreach ($project->Sections as $section) {
            if (method_exists($section, 'trashed') && $section->trashed()) {
                continue;
            }

            foreach ($section->ContractItems as $ci) {
                if (method_exists($ci, 'trashed') && $ci->trashed()) {
                    continue;
                }
                if ((int) $ci->exclude_from_monitoring === 1) {
                    continue;
                }

                $scope = (float) $ci->contract_quantity;
                $price = (float) $ci->contract_unit_price;
                $ciContractAmount = $scope * $price;
                $ciWeight = $totalContractValue > 0 ? ($ciContractAmount / $totalContractValue) : 0.0;

                $latestActualAcc = null;
                $latestTargetAcc = null;
                $latestNextTargetAcc = null;

                if ($ci->Accomplishments) {
                    foreach ($ci->Accomplishments as $acc) {
                        if (! $acc->entry_data) {
                            continue;
                        }
                        $accDateStr = Carbon::parse($acc->entry_data)->format('Y-m-d');

                        if ($acc->type === 'ACTUAL' && $accDateStr <= $asOfDate) {
                            $currDateStr = $latestActualAcc ? Carbon::parse($latestActualAcc->entry_data)->format('Y-m-d') : '';
                            if (! $latestActualAcc || $accDateStr > $currDateStr || ($accDateStr === $currDateStr && $acc->id > $latestActualAcc->id)) {
                                $latestActualAcc = $acc;
                            }
                        }

                        if ($acc->type === 'TARGET' && $accDateStr <= $asOfDate) {
                            $currDateStr = $latestTargetAcc ? Carbon::parse($latestTargetAcc->entry_data)->format('Y-m-d') : '';
                            if (! $latestTargetAcc || $accDateStr > $currDateStr || ($accDateStr === $currDateStr && $acc->id > $latestTargetAcc->id)) {
                                $latestTargetAcc = $acc;
                            }
                        }

                        if ($acc->type === 'TARGET' && $nextTargetDate && $accDateStr <= $nextTargetDate) {
                            $currNextDateStr = $latestNextTargetAcc ? Carbon::parse($latestNextTargetAcc->entry_data)->format('Y-m-d') : '';
                            if (! $latestNextTargetAcc || $accDateStr > $currNextDateStr || ($accDateStr === $currNextDateStr && $acc->id > $latestNextTargetAcc->id)) {
                                $latestNextTargetAcc = $acc;
                            }
                        }
                    }
                }

                $actualQty = $latestActualAcc ? (float) $latestActualAcc->quantity : 0.0;
                $targetQty = $latestTargetAcc ? (float) $latestTargetAcc->quantity : 0.0;
                $nextTargetQty = $latestNextTargetAcc ? (float) $latestNextTargetAcc->quantity : 0.0;

                if ($latestActualAcc) {
                    $accDateStr = Carbon::parse($latestActualAcc->entry_data)->format('Y-m-d');
                    if (! $actualLatestDateAsOf || $accDateStr > $actualLatestDateAsOf) {
                        $actualLatestDateAsOf = $accDateStr;
                    }
                }

                $percentActual = $scope > 0 ? min(100.0, ($actualQty / $scope) * 100.0) : 0.0;
                $percentTarget = $scope > 0 ? min(100.0, ($targetQty / $scope) * 100.0) : 0.0;
                $percentNextTarget = $scope > 0 ? min(100.0, ($nextTargetQty / $scope) * 100.0) : 0.0;

                $weightedActual = $percentActual * $ciWeight;
                $weightedTarget = $percentTarget * $ciWeight;
                $weightedNextTarget = $percentNextTarget * $ciWeight;

                $sumWeightedActual += $weightedActual;
                $sumWeightedTarget += $weightedTarget;
                $sumNextWeightedTarget += $weightedNextTarget;

                $totalAccomplishedAmount += ($actualQty * $price);
                $totalTargetAmount += ($targetQty * $price);

                $ciName = trim(($ci->item_code ? "[{$ci->item_code}] " : '') . ($ci->description ?? $ci->name ?? ''));

                $itemsBreakdown[] = [
                    'contract_item_id' => $ci->id,
                    'item_code' => $ci->item_code,
                    'description' => $ci->description,
                    'name' => $ciName,
                    'contract_quantity' => $scope,
                    'contract_unit_price' => $price,
                    'contract_amount' => round($ciContractAmount, 2),
                    'weight_percent' => round($ciWeight * 100.0, 4),
                    'latest_actual_quantity' => $actualQty,
                    'actual_percent' => round($percentActual, 2),
                    'weighted_actual_percent' => round($weightedActual, 4),
                    'accomplished_amount' => round($actualQty * $price, 2),
                    'latest_target_quantity' => $targetQty,
                    'target_percent' => round($percentTarget, 2),
                    'weighted_target_percent' => round($weightedTarget, 4),
                    'target_amount' => round($targetQty * $price, 2),
                ];
            }
        }

        $overallActual = round($sumWeightedActual, 2);
        $overallTarget = round($sumWeightedTarget, 2);
        $slippage = round($overallActual - $overallTarget, 2);

        if ($slippage > 0) {
            $slippageText = sprintf('+%.2f%% Ahead', $slippage);
        } elseif ($slippage < 0) {
            $slippageText = sprintf('%.2f%% Behind', $slippage);
        } else {
            $slippageText = '0.00% On Schedule';
        }

        $financialProgressPercent = $totalContractValue > 0
            ? round(min(100.0, ($totalAccomplishedAmount / $totalContractValue) * 100.0), 2)
            : 0.0;

        // Running velocity across the project lifecycle up to as_of_date
        $runningDaysElapsed = 0;
        $runningVelocityPerDay = 0.0;
        $estCompletionDate = null;
        $estCompletionText = 'Insufficient Data';

        $velocityEndDate = $actualLatestDateAsOf ?? $asOfDate;
        if ($projectFirstActualDate && $velocityEndDate && $projectFirstActualDate <= $velocityEndDate) {
            $dStart = Carbon::parse($projectFirstActualDate)->startOfDay();
            $dEnd = Carbon::parse($velocityEndDate)->startOfDay();
            $runningDaysElapsed = max(1, (int) $dStart->diffInDays($dEnd) + 1);
            $runningVelocityPerDay = $runningDaysElapsed > 0 ? $overallActual / $runningDaysElapsed : 0.0;

            if ($overallActual >= 100.0) {
                $estCompletionDate = 'Completed';
                $estCompletionText = 'Completed';
            } elseif ($runningVelocityPerDay > 0) {
                $remainingPercent = 100.0 - $overallActual;
                $remainingDays = (int) ceil($remainingPercent / $runningVelocityPerDay);
                $eta = Carbon::parse($asOfDate)->copy()->addDays($remainingDays);
                $estCompletionDate = $eta->format('Y-m-d');
                $estCompletionText = sprintf('%s (~%d days)', $eta->format('M j, Y'), $remainingDays);
            }
        } elseif ($overallActual >= 100.0) {
            $estCompletionDate = 'Completed';
            $estCompletionText = 'Completed';
        }

        $runningSpeedFormatted = $runningVelocityPerDay > 0
            ? sprintf('%.2f%%/day', $runningVelocityPerDay)
            : '0.00%/day';

        // Required speed to hit target completion date or 100%
        $requiredVelocityPerDay = 0.0;
        $projectDaysLeft = 0;
        $requiredSpeedFormatted = 'N/A';

        if ($targetDateToUse && $overallActual < 100.0) {
            $dTarget = Carbon::parse($targetDateToUse)->startOfDay();
            $dCutoff = Carbon::parse($asOfDate)->startOfDay();
            $daysDiff = (int) $dCutoff->diffInDays($dTarget, false);
            $projectDaysLeft = $daysDiff > 0 ? $daysDiff : 1;
            $remainingPercent = 100.0 - $overallActual;
            $requiredVelocityPerDay = $remainingPercent / $projectDaysLeft;
            $requiredSpeedFormatted = sprintf(
                '%.2f%%/day for %d day%s',
                $requiredVelocityPerDay,
                $projectDaysLeft,
                $projectDaysLeft === 1 ? '' : 's'
            );
        }

        $nextTargetPercent = $nextTargetDate ? round($sumNextWeightedTarget, 2) : null;
        $nextTargetText = $nextTargetDate ? sprintf('%.2f%% on %s', $nextTargetPercent, $nextTargetDate) : 'N/A';

        return ToolResult::json([
            'project_id' => $project->id,
            'project_name' => $project->name,
            'as_of_date' => $asOfDate,
            'is_as_of_date_inferred' => $isDateInferred,
            'total_contract_value' => round($totalContractValue, 2),
            'overall_actual' => $overallActual,
            'overall_target' => $overallTarget,
            'slippage' => $slippage,
            'slippage_text' => $slippageText,
            'accomplished_amount' => round($totalAccomplishedAmount, 2),
            'target_amount' => round($totalTargetAmount, 2),
            'financial_progress_percent' => $financialProgressPercent,
            'running_speed' => round($runningVelocityPerDay, 4),
            'running_speed_text' => $runningSpeedFormatted,
            'running_days_elapsed' => $runningDaysElapsed,
            'est_completion_date' => $estCompletionDate,
            'est_completion_text' => $estCompletionText,
            'next_target_date' => $nextTargetDate,
            'next_target_percent' => $nextTargetPercent,
            'next_target_text' => $nextTargetText,
            'required_speed' => round($requiredVelocityPerDay, 4),
            'required_speed_text' => $requiredSpeedFormatted,
            'project_days_left' => $projectDaysLeft,
            'monitored_items_count' => $monitoredItemsCount,
            'items_breakdown' => $itemsBreakdown,
        ]);
    }
}
