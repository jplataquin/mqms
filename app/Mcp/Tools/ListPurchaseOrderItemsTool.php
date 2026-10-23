<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\McpModelRegistry;
use App\Models\PurchaseOrderItem;
use Generator;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('List Purchase Order Items')]
class ListPurchaseOrderItemsTool extends Tool
{
    public function name(): string
    {
        return 'list_purchase_order_items';
    }

    public function description(): string
    {
        return 'List and filter standard purchase order line items. Supports filtering by item ID, PO ID, component item ID, request item ID, canvass ID, material item ID, and status.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        $schema->integer('id')
            ->description('Optional item ID to match exactly.')
            ->optional();

        $schema->integer('purchase_order_id')
            ->description('Optional parent purchase order ID to filter by.')
            ->optional();

        $schema->integer('component_item_id')
            ->description('Optional component item ID to filter by.')
            ->optional();

        $schema->integer('material_quantity_request_item_id')
            ->description('Optional material quantity request item ID to filter by.')
            ->optional();

        $schema->integer('material_canvass_id')
            ->description('Optional material canvass ID to filter by.')
            ->optional();

        $schema->integer('material_item_id')
            ->description('Optional material item ID to filter by.')
            ->optional();

        $schema->string('status')
            ->description('Optional status code (e.g., PEND, APRV, RJCT).')
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
        $poId = isset($arguments['purchase_order_id']) && $arguments['purchase_order_id'] !== '' ? (int) $arguments['purchase_order_id'] : null;
        $componentItemId = isset($arguments['component_item_id']) && $arguments['component_item_id'] !== '' ? (int) $arguments['component_item_id'] : null;
        $requestItemId = isset($arguments['material_quantity_request_item_id']) && $arguments['material_quantity_request_item_id'] !== '' ? (int) $arguments['material_quantity_request_item_id'] : null;
        $canvassId = isset($arguments['material_canvass_id']) && $arguments['material_canvass_id'] !== '' ? (int) $arguments['material_canvass_id'] : null;
        $materialItemId = isset($arguments['material_item_id']) && $arguments['material_item_id'] !== '' ? (int) $arguments['material_item_id'] : null;
        $status = isset($arguments['status']) ? trim((string) $arguments['status']) : '';

        $page = max(1, (int) ($arguments['page'] ?? 1));
        $limit = min(100, max(1, (int) ($arguments['limit'] ?? 15)));
        $orderBy = (string) ($arguments['order_by'] ?? 'id');
        $order = strtoupper((string) ($arguments['order'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $modelInstance = new PurchaseOrderItem();
        $table = $modelInstance->getTable();
        $schema = $modelInstance->getConnection()->getSchemaBuilder();
        if (! $schema->hasColumn($table, $orderBy)) {
            $orderBy = 'id';
        }

        $query = PurchaseOrderItem::query();

        if (! is_null($id)) {
            $query->where('id', $id);
        }

        if (! is_null($poId)) {
            $query->where('purchase_order_id', $poId);
        }

        if (! is_null($componentItemId)) {
            $query->where('component_item_id', $componentItemId);
        }

        if (! is_null($requestItemId)) {
            $query->where('material_quantity_request_item_id', $requestItemId);
        }

        if (! is_null($canvassId)) {
            $query->where('material_canvass_id', $canvassId);
        }

        if (! is_null($materialItemId)) {
            $query->where('material_item_id', $materialItemId);
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
            'model' => 'purchase_order_item',
            'filters_applied' => array_filter([
                'id' => $id,
                'purchase_order_id' => $poId,
                'component_item_id' => $componentItemId,
                'material_quantity_request_item_id' => $requestItemId,
                'material_canvass_id' => $canvassId,
                'material_item_id' => $materialItemId,
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
