<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\McpModelRegistry;
use App\Models\MaterialCanvass;
use Generator;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('List Material Canvasses')]
class ListMaterialCanvassesTool extends Tool
{
    public function name(): string
    {
        return 'list_material_canvasses';
    }

    public function description(): string
    {
        return 'List and filter standard material canvasses. Supports filtering by ID, request ID, request item ID, supplier ID, payment term ID, and status.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        $schema->integer('id')
            ->description('Optional canvass ID to match exactly.')
            ->optional();

        $schema->integer('material_quantity_request_id')
            ->description('Optional material quantity request ID to filter by.')
            ->optional();

        $schema->integer('material_quantity_request_item_id')
            ->description('Optional material quantity request item ID to filter by.')
            ->optional();

        $schema->integer('supplier_id')
            ->description('Optional supplier ID to filter by.')
            ->optional();

        $schema->integer('payment_term_id')
            ->description('Optional payment term ID to filter by.')
            ->optional();

        $schema->string('status')
            ->description('Optional status code (e.g., PEND, APRV, RJCT, VOID).')
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
        $requestId = isset($arguments['material_quantity_request_id']) && $arguments['material_quantity_request_id'] !== '' ? (int) $arguments['material_quantity_request_id'] : null;
        $requestItemId = isset($arguments['material_quantity_request_item_id']) && $arguments['material_quantity_request_item_id'] !== '' ? (int) $arguments['material_quantity_request_item_id'] : null;
        $supplierId = isset($arguments['supplier_id']) && $arguments['supplier_id'] !== '' ? (int) $arguments['supplier_id'] : null;
        $paymentTermId = isset($arguments['payment_term_id']) && $arguments['payment_term_id'] !== '' ? (int) $arguments['payment_term_id'] : null;
        $status = isset($arguments['status']) ? trim((string) $arguments['status']) : '';

        $page = max(1, (int) ($arguments['page'] ?? 1));
        $limit = min(100, max(1, (int) ($arguments['limit'] ?? 15)));
        $orderBy = (string) ($arguments['order_by'] ?? 'id');
        $order = strtoupper((string) ($arguments['order'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $modelInstance = new MaterialCanvass();
        $table = $modelInstance->getTable();
        $schema = $modelInstance->getConnection()->getSchemaBuilder();
        if (! $schema->hasColumn($table, $orderBy)) {
            $orderBy = 'id';
        }

        $query = MaterialCanvass::query();

        if (! is_null($id)) {
            $query->where('id', $id);
        }

        if (! is_null($requestId)) {
            $query->where('material_quantity_request_id', $requestId);
        }

        if (! is_null($requestItemId)) {
            $query->where('material_quantity_request_item_id', $requestItemId);
        }

        if (! is_null($supplierId)) {
            $query->where('supplier_id', $supplierId);
        }

        if (! is_null($paymentTermId)) {
            $query->where('payment_term_id', $paymentTermId);
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $total = $query->count();
        $records = $query->orderBy($orderBy, $order)
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $records->map(fn ($r) => McpModelRegistry::sanitize($r))->toArray();

        return ToolResult::json([
            'model' => 'material_canvass',
            'filters_applied' => array_filter([
                'id' => $id,
                'material_quantity_request_id' => $requestId,
                'material_quantity_request_item_id' => $requestItemId,
                'supplier_id' => $supplierId,
                'payment_term_id' => $paymentTermId,
                'status' => $status !== '' ? $status : null,
            ], fn ($v) => ! is_null($v)),
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit),
            'records' => $data,
        ]);
    }
}
