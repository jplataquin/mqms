<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Project;
use App\Protocols\LevelTwoBudgetDiagnostic;
use App\Protocols\ProtocolRegistry;
use Generator;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('Run Level 2 Budget Diagnostic')]
class RunLevelTwoBudgetDiagnosticTool extends Tool
{
    public function name(): string
    {
        return 'run_level_two_budget_diagnostic';
    }

    public function description(): string
    {
        return 'Executes the Level 2 Budget Diagnostic protocol for a project. Verifies that all material quantities in approved purchase orders are within approved Material Quantity Request quantities, and verifies that the total amount of approved purchase orders per contract item does not exceed the contract item amount, calculating the percentage used.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        $schema->integer('project_id')
            ->description('The primary key ID of the project to run the diagnostic on.')
            ->required();

        $schema->integer('contract_item_id')
            ->description('Optional contract item ID to restrict diagnostics to a single contract item.');

        return $schema;
    }

    public function handle(array $arguments): ToolResult|Generator
    {
        $projectId = isset($arguments['project_id']) && $arguments['project_id'] !== ''
            ? (int) $arguments['project_id']
            : null;

        if (! $projectId) {
            return ToolResult::error('project_id is required.');
        }

        $project = Project::find($projectId);
        if (! $project) {
            return ToolResult::error("Project with ID {$projectId} not found.");
        }

        $params = [
            'project_id' => $projectId,
        ];

        if (isset($arguments['contract_item_id']) && $arguments['contract_item_id'] !== '') {
            $params['contract_item_id'] = (int) $arguments['contract_item_id'];
        }

        $result = ProtocolRegistry::run(LevelTwoBudgetDiagnostic::SLUG, $params);

        return ToolResult::json($result);
    }
}
