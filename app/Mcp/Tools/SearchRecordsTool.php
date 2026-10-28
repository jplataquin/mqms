<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\McpModelRegistry;
use Generator;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('Search Records')]
class SearchRecordsTool extends Tool
{
    use HasDynamicDescription;

    public function name(): string
    {
        return 'search_records';
    }

    public function defaultDescription(): string
    {
        return 'Search records in a model by keyword across common text fields (e.g., name, remarks, description, text, brand). Available models: ' . implode(', ', McpModelRegistry::getAllowedModelKeys()) . '.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        $schema->string('model')
            ->description('The name of the model to search in.')
            ->required();

        $schema->string('query')
            ->description('The search query or keyword.')
            ->required();

        $schema->integer('page')
            ->description('The page number to retrieve (default: 1).')
            ->optional();

        $schema->integer('limit')
            ->description('Number of records per page (default: 15, max: 100).')
            ->optional();

        return $schema;
    }

    public function handle(array $arguments): ToolResult|Generator
    {
        $modelName = $arguments['model'] ?? '';
        $queryText = trim((string) ($arguments['query'] ?? ''));

        if (! McpModelRegistry::isAllowed((string) $modelName)) {
            return ToolResult::error("Model '{$modelName}' is not allowed or does not exist.");
        }

        if ($queryText === '') {
            return ToolResult::error("Search query cannot be empty.");
        }

        $page = max(1, (int) ($arguments['page'] ?? 1));
        $limit = min(100, max(1, (int) ($arguments['limit'] ?? 15)));

        $modelClass = McpModelRegistry::getModelClass((string) $modelName);
        $modelInstance = new $modelClass;
        $table = $modelInstance->getTable();
        $schema = $modelInstance->getConnection()->getSchemaBuilder();
        $columns = $schema->getColumnListing($table);

        $searchableCandidates = [
            'name', 'title', 'description', 'remarks', 'text', 'brand',
            'code', 'plate_no', 'first_name', 'last_name', 'email', 'address', 'specification_unit_packaging'
        ];
        $searchable = array_values(array_intersect($columns, $searchableCandidates));

        $query = $modelClass::query();

        if (is_numeric($queryText) && in_array('id', $columns, true)) {
            $query->where(function ($q) use ($queryText, $searchable) {
                $q->where('id', '=', (int) $queryText);
                foreach ($searchable as $col) {
                    $q->orWhere($col, 'LIKE', '%' . $queryText . '%');
                }
            });
        } elseif (! empty($searchable)) {
            $query->where(function ($q) use ($queryText, $searchable) {
                foreach ($searchable as $index => $col) {
                    if ($index === 0) {
                        $q->where($col, 'LIKE', '%' . $queryText . '%');
                    } else {
                        $q->orWhere($col, 'LIKE', '%' . $queryText . '%');
                    }
                }
            });
        }

        $total = $query->count();
        $records = $query->orderBy($modelInstance->getKeyName() ?: 'id', 'DESC')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $records->map(fn ($r) => McpModelRegistry::sanitize($r))->toArray();

        return ToolResult::json([
            'model' => $modelName,
            'query' => $queryText,
            'searchable_fields' => $searchable,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit),
            'records' => $data,
        ]);
    }
}
