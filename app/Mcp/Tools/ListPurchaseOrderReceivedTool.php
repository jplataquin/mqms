<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\McpModelRegistry;
use App\Models\PurchaseOrderReceived;
use Generator;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('List Purchase Order Received')]
class ListPurchaseOrderReceivedTool extends Tool
{
    use HasDynamicDescription;

    public function name(): string
    {
        return 'list_purchase_order_received';
    }

    public function defaultDescription(): string
    {
        return 'List and filter standard purchase order receipts / deliveries. Supports filtering by receipt ID, PO ID, receipt number (partial match), and received date range.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        $schema->integer('id')
            ->description('Optional receipt ID to match exactly.')
            ->optional();

        $schema->integer('purchase_order_id')
            ->description('Optional purchase order ID to filter receipts by.')
            ->optional();

        $schema->string('receipt_no')
            ->description('Optional receipt number or keyword to search (partial match).')
            ->optional();

        $schema->string('date_from')
            ->description('Optional start date (YYYY-MM-DD) for received_date.')
            ->optional();

        $schema->string('date_to')
            ->description('Optional end date (YYYY-MM-DD) for received_date.')
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
        $receiptNo = isset($arguments['receipt_no']) ? trim((string) $arguments['receipt_no']) : '';
        $dateFrom = ! empty($arguments['date_from']) ? trim((string) $arguments['date_from']) : null;
        $dateTo = ! empty($arguments['date_to']) ? trim((string) $arguments['date_to']) : null;

        $page = max(1, (int) ($arguments['page'] ?? 1));
        $limit = min(100, max(1, (int) ($arguments['limit'] ?? 15)));
        $orderBy = (string) ($arguments['order_by'] ?? 'id');
        $order = strtoupper((string) ($arguments['order'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $modelInstance = new PurchaseOrderReceived();
        $table = $modelInstance->getTable();
        $schema = $modelInstance->getConnection()->getSchemaBuilder();
        if (! $schema->hasColumn($table, $orderBy)) {
            $orderBy = 'id';
        }

        $query = PurchaseOrderReceived::query();

        if (! is_null($id)) {
            $query->where('id', $id);
        }

        if (! is_null($poId)) {
            $query->where('purchase_order_id', $poId);
        }

        if ($receiptNo !== '') {
            $query->where('receipt_no', 'LIKE', '%' . $receiptNo . '%');
        }

        if ($dateFrom) {
            $query->where('received_date', '>=', $dateFrom . ' 00:00:00');
        }

        if ($dateTo) {
            $query->where('received_date', '<=', $dateTo . ' 23:59:59');
        }

        $total = $query->count();
        $records = $query->orderBy($orderBy, $order)
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $records->map(fn ($r) => McpModelRegistry::sanitize($r))->toArray();

        return ToolResult::json([
            'model' => 'purchase_order_received',
            'filters_applied' => array_filter([
                'id' => $id,
                'purchase_order_id' => $poId,
                'receipt_no' => $receiptNo !== '' ? $receiptNo : null,
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
