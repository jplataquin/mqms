<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Project;
use App\Protocols\LevelOneBudgetDiagnostic;
use App\Protocols\ProtocolRegistry;
use Generator;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('Run Level 1 Budget Diagnostic')]
class RunLevelOneBudgetDiagnosticTool extends Tool
{
    use HasDynamicDescription;

    public function name(): string
    {
        return 'run_level_one_budget_diagnostic';
    }

    public function defaultDescription(): string
    {
        return 'Executes the Level 1 Budget Diagnostic protocol for a project. Verifies that total requested quantity (scaled by equivalent) in approved material quantity requests does not exceed component item budget. Supports optional filtering by component_id, component_item_id, include_closed, and only_approved_components.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        $schema->integer('project_id')
            ->description('The primary key ID of the project to run the diagnostic on.')
            ->required();

        $schema->integer('component_id')
            ->description('Optional component ID to restrict diagnostics to a single component.');

        $schema->integer('component_item_id')
            ->description('Optional component item ID to restrict diagnostics to a single component item.');

        $schema->boolean('include_closed')
            ->description('Optional flag to include closed (CLSD) material requests alongside approved (APRV) ones. Defaults to true.');

        $schema->boolean('only_approved_components')
            ->description('Optional flag to check only components with status APRV. Defaults to false.');

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

        if (isset($arguments['component_id']) && $arguments['component_id'] !== '') {
            $params['component_id'] = (int) $arguments['component_id'];
        }

        if (isset($arguments['component_item_id']) && $arguments['component_item_id'] !== '') {
            $params['component_item_id'] = (int) $arguments['component_item_id'];
        }

        if (isset($arguments['include_closed'])) {
            $params['include_closed'] = filter_var($arguments['include_closed'], FILTER_VALIDATE_BOOLEAN);
        }

        if (isset($arguments['only_approved_components'])) {
            $params['only_approved_components'] = filter_var($arguments['only_approved_components'], FILTER_VALIDATE_BOOLEAN);
        }

        $result = ProtocolRegistry::run(LevelOneBudgetDiagnostic::SLUG, $params);

        return ToolResult::json($result);
    }
}
