<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\McpModelRegistry;
use Generator;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('Get Record')]
class GetRecordTool extends Tool
{
    public function name(): string
    {
        return 'get_record';
    }

    public function description(): string
    {
        return 'Get a single record by its model name and ID. Available models: ' . implode(', ', McpModelRegistry::getAllowedModelKeys()) . '.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        $schema->string('model')
            ->description('The name of the model to retrieve (e.g. project, material_item, user).')
            ->required();

        $schema->integer('id')
            ->description('The primary key ID of the record.')
            ->required();

        return $schema;
    }

    public function handle(array $arguments): ToolResult|Generator
    {
        $modelName = $arguments['model'] ?? '';
        $id = $arguments['id'] ?? null;

        if (! McpModelRegistry::isAllowed((string) $modelName)) {
            return ToolResult::error("Model '{$modelName}' is not allowed or does not exist.");
        }

        if (is_null($id)) {
            return ToolResult::error("Record ID is required.");
        }

        $modelClass = McpModelRegistry::getModelClass((string) $modelName);
        $record = $modelClass::find($id);

        if (! $record) {
            return ToolResult::error("Record not found for {$modelName} with ID {$id}.");
        }

        $cleanData = McpModelRegistry::sanitize($record);

        return ToolResult::json($cleanData);
    }
}
