<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\McpModelRegistry;
use Generator;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('List Records')]
class ListRecordsTool extends Tool
{
    public function name(): string
    {
        return 'list_records';
    }

    public function description(): string
    {
        return 'List paginated records for a specified model. Available models: ' . implode(', ', McpModelRegistry::getAllowedModelKeys()) . '.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        $schema->string('model')
            ->description('The name of the model to list records for (e.g. project, material_item, supplier).')
            ->required();

        $schema->integer('page')
            ->description('The page number to retrieve (default: 1).')
            ->optional();

        $schema->integer('limit')
            ->description('Number of records per page (default: 15, max: 100).')
            ->optional();

        $schema->string('order_by')
            ->description('Column name to sort by (default: id).')
            ->optional();

        $schema->string('order')
            ->description('Sort direction: ASC or DESC (default: DESC).')
            ->optional();

        return $schema;
    }

    public function handle(array $arguments): ToolResult|Generator
    {
        $modelName = $arguments['model'] ?? '';

        if (! McpModelRegistry::isAllowed((string) $modelName)) {
            return ToolResult::error("Model '{$modelName}' is not allowed or does not exist.");
        }

        $page = max(1, (int) ($arguments['page'] ?? 1));
        $limit = min(100, max(1, (int) ($arguments['limit'] ?? 15)));
        $orderBy = (string) ($arguments['order_by'] ?? 'id');
        $order = strtoupper((string) ($arguments['order'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $modelClass = McpModelRegistry::getModelClass((string) $modelName);
        $modelInstance = new $modelClass;

        // Security check: ensure order_by is an actual column or default to id
        $table = $modelInstance->getTable();
        $schema = $modelInstance->getConnection()->getSchemaBuilder();
        if (! $schema->hasColumn($table, $orderBy)) {
            $orderBy = $modelInstance->getKeyName() ?: 'id';
        }

        $query = $modelClass::orderBy($orderBy, $order);
        $total = $query->count();
        $records = $query->skip(($page - 1) * $limit)->take($limit)->get();

        $data = $records->map(fn ($r) => McpModelRegistry::sanitize($r))->toArray();

        return ToolResult::json([
            'model' => $modelName,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit),
            'records' => $data,
        ]);
    }
}
