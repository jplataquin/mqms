<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Tools\Annotations\Title;

#[Title('Level 2 Budget Diagnostic')]
class LevelTwoBudgetDiagnosticTool extends RunLevelTwoBudgetDiagnosticTool
{
    public function name(): string
    {
        return 'level_two_budget_diagnostic';
    }
}
