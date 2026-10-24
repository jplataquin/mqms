<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Tools\Annotations\Title;

#[Title('Level 1 Budget Diagnostic')]
class LevelOneBudgetDiagnosticTool extends RunLevelOneBudgetDiagnosticTool
{
    public function name(): string
    {
        return 'level_one_budget_diagnostic';
    }
}
