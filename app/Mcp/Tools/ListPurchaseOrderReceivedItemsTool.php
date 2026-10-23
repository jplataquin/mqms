<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\McpModelRegistry;
use App\Models\PurchaseOrderReceivedItem;
use Generator;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('List Purchase Order Received Items')]
class ListPurchaseOrderReceivedItemsTool extends Tool
{
    public function name(): string
    {
        return 'list_purchase_order_received_items';
    }

    public function description(): string
    {
        return 'List and filter items within purchase order receipts / deliveries. Supports filtering by receipt item ID, receipt ID, and PO item ID.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        $schema->integer('id')
            ->description('Optional receipt item ID to match exactly.')
            ->optional();

        $schema->integer('purchase_order_received_id')
            ->description('Optional parent purchase order receipt ID to filter by.')
            ->optional();

        $schema->integer('purchase_order_item_id')
            ->description('Optional purchase order item ID to filter by.')
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
        $receivedId = isset($arguments['purchase_order_received_id']) && $arguments['purchase_order_received_id'] !== '' ? (int) $arguments['purchase_order_received_id'] : null;
        $poItemId = isset($arguments['purchase_order_item_id']) && $arguments['purchase_order_item_id'] !== '' ? (int) $arguments['purchase_order_item_id'] : null;

        $page = max(1, (int) ($arguments['page'] ?? 1));
        $limit = min(100, max(1, (int) ($arguments['limit'] ?? 15)));
        $orderBy = (string) ($arguments['order_by'] ?? 'id');
        $order = strtoupper((string) ($arguments['order'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $modelInstance = new PurchaseOrderReceivedItem();
        $table = $modelInstance->getTable();
        $schema = $modelInstance->getConnection()->getSchemaBuilder();
        if (! $schema->hasColumn($table, $orderBy)) {
            $orderBy = 'id';
        }

        $query = PurchaseOrderReceivedItem::query();

        if (! is_null($id)) {
            $query->where('id', $id);
        }

        if (! is_null($receivedId)) {
            $query->where('purchase_order_received_id', $receivedId);
        }

        if (! is_null($poItemId)) {
            $query->where('purchase_order_item_id', $poItemId);
        }

        $total = $query->count();
        $records = $query->orderBy($orderBy, $order)
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $records->map(fn ($r) => McpModelRegistry::sanitize($r))->toArray();

        return ToolResult::json([
            'model' => 'purchase_order_received_item',
            'filters_applied' => array_filter([
                'id' => $id,
                'purchase_order_received_id' => $receivedId,
                'purchase_order_item_id' => $poItemId,
            ], fn ($v) => ! is_null($v)),
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit),
            'records' => $data,
        ]);
    }
}
