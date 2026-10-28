<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\McpModelRegistry;
use App\Models\MaterialQuantityRequest;
use Generator;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('List Material Quantity Requests')]
class ListMaterialQuantityRequestsTool extends Tool
{
    use HasDynamicDescription;

    public function name(): string
    {
        return 'list_material_quantity_requests';
    }

    public function defaultDescription(): string
    {
        return 'List and filter material quantity requests (MQRs). Supports filtering by ID, project ID, section ID, component ID, status, creator, and creation date range.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        $schema->integer('id')
            ->description('Optional request ID to match exactly.')
            ->optional();

        $schema->integer('project_id')
            ->description('Optional project ID to filter requests by.')
            ->optional();

        $schema->integer('section_id')
            ->description('Optional section ID to filter requests by.')
            ->optional();

        $schema->integer('component_id')
            ->description('Optional component ID to filter requests by.')
            ->optional();

        $schema->string('status')
            ->description('Optional status code (e.g., PEND, APRV, RJCT).')
            ->optional();

        $schema->integer('created_by')
            ->description('Optional user ID who created the request.')
            ->optional();

        $schema->string('date_from')
            ->description('Optional start date (YYYY-MM-DD) for request creation.')
            ->optional();

        $schema->string('date_to')
            ->description('Optional end date (YYYY-MM-DD) for request creation.')
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
        $projectId = isset($arguments['project_id']) && $arguments['project_id'] !== '' ? (int) $arguments['project_id'] : null;
        $sectionId = isset($arguments['section_id']) && $arguments['section_id'] !== '' ? (int) $arguments['section_id'] : null;
        $componentId = isset($arguments['component_id']) && $arguments['component_id'] !== '' ? (int) $arguments['component_id'] : null;
        $status = isset($arguments['status']) ? trim((string) $arguments['status']) : '';
        $createdBy = isset($arguments['created_by']) && $arguments['created_by'] !== '' ? (int) $arguments['created_by'] : null;
        $dateFrom = ! empty($arguments['date_from']) ? trim((string) $arguments['date_from']) : null;
        $dateTo = ! empty($arguments['date_to']) ? trim((string) $arguments['date_to']) : null;

        $page = max(1, (int) ($arguments['page'] ?? 1));
        $limit = min(100, max(1, (int) ($arguments['limit'] ?? 15)));
        $orderBy = (string) ($arguments['order_by'] ?? 'id');
        $order = strtoupper((string) ($arguments['order'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $modelInstance = new MaterialQuantityRequest();
        $table = $modelInstance->getTable();
        $schema = $modelInstance->getConnection()->getSchemaBuilder();
        if (! $schema->hasColumn($table, $orderBy)) {
            $orderBy = 'id';
        }

        $query = MaterialQuantityRequest::query();

        if (! is_null($id)) {
            $query->where('id', $id);
        }

        if (! is_null($projectId)) {
            $query->where('project_id', $projectId);
        }

        if (! is_null($sectionId)) {
            $query->where('section_id', $sectionId);
        }

        if (! is_null($componentId)) {
            $query->where('component_id', $componentId);
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
            'model' => 'material_quantity_request',
            'filters_applied' => array_filter([
                'id' => $id,
                'project_id' => $projectId,
                'section_id' => $sectionId,
                'component_id' => $componentId,
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
