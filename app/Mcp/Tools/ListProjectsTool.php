<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\McpModelRegistry;
use App\Models\Project;
use Generator;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('List Projects')]
class ListProjectsTool extends Tool
{
    use HasDynamicDescription;

    public function name(): string
    {
        return 'list_projects';
    }

    public function defaultDescription(): string
    {
        return 'List and filter projects. Supports filtering by ID, name (partial match), status, creator, and creation date range.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        $schema->integer('id')
            ->description('Optional project ID to match exactly.')
            ->optional();

        $schema->string('name')
            ->description('Optional project name or keyword to search (partial match).')
            ->optional();

        $schema->string('status')
            ->description('Optional project status (e.g., ACTV, ARCH).')
            ->optional();

        $schema->integer('created_by')
            ->description('Optional user ID who created the project.')
            ->optional();

        $schema->string('date_from')
            ->description('Optional start date (YYYY-MM-DD) for project creation.')
            ->optional();

        $schema->string('date_to')
            ->description('Optional end date (YYYY-MM-DD) for project creation.')
            ->optional();

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
        $id = isset($arguments['id']) && $arguments['id'] !== '' ? (int) $arguments['id'] : null;
        $name = isset($arguments['name']) ? trim((string) $arguments['name']) : '';
        $status = isset($arguments['status']) ? trim((string) $arguments['status']) : '';
        $createdBy = isset($arguments['created_by']) && $arguments['created_by'] !== '' ? (int) $arguments['created_by'] : null;
        $dateFrom = ! empty($arguments['date_from']) ? trim((string) $arguments['date_from']) : null;
        $dateTo = ! empty($arguments['date_to']) ? trim((string) $arguments['date_to']) : null;

        $page = max(1, (int) ($arguments['page'] ?? 1));
        $limit = min(100, max(1, (int) ($arguments['limit'] ?? 15)));
        $orderBy = (string) ($arguments['order_by'] ?? 'id');
        $order = strtoupper((string) ($arguments['order'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $modelInstance = new Project();
        $table = $modelInstance->getTable();
        $schema = $modelInstance->getConnection()->getSchemaBuilder();
        if (! $schema->hasColumn($table, $orderBy)) {
            $orderBy = 'id';
        }

        $query = Project::query();

        if (! is_null($id)) {
            $query->where('id', $id);
        }

        if ($name !== '') {
            $query->where('name', 'LIKE', '%' . $name . '%');
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if (! is_null($createdBy)) {
            $query->where('created_by', $createdBy);
        }

        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom . ' 00:00:00');
        }

        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        $total = $query->count();
        $records = $query->orderBy($orderBy, $order)
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $records->map(fn ($r) => McpModelRegistry::sanitize($r))->toArray();

        return ToolResult::json([
            'model' => 'project',
            'filters_applied' => array_filter([
                'id' => $id,
                'name' => $name !== '' ? $name : null,
                'status' => $status !== '' ? $status : null,
                'created_by' => $createdBy,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ], fn ($v) => ! is_null($v)),
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit),
            'records' => $data,
        ]);
    }
}
