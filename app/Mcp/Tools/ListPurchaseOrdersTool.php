<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\McpModelRegistry;
use App\Models\PurchaseOrder;
use Generator;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('List Purchase Orders')]
class ListPurchaseOrdersTool extends Tool
{
    public function name(): string
    {
        return 'list_purchase_orders';
    }

    public function description(): string
    {
        return 'List and filter standard purchase orders. Supports filtering by PO ID, project ID, section ID, component ID, request ID, supplier ID, payment term ID, status, received status, and creation date range.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        $schema->integer('id')
            ->description('Optional purchase order ID to match exactly.')
            ->optional();

        $schema->integer('project_id')
            ->description('Optional project ID to filter POs by.')
            ->optional();

        $schema->integer('section_id')
            ->description('Optional section ID to filter POs by.')
            ->optional();

        $schema->integer('contract_item_id')
            ->description('Optional contract item ID to filter POs by.')
            ->optional();

        $schema->integer('component_id')
            ->description('Optional component ID to filter POs by.')
            ->optional();

        $schema->integer('material_quantity_request_id')
            ->description('Optional material quantity request ID to filter POs by.')
            ->optional();

        $schema->integer('supplier_id')
            ->description('Optional supplier ID to filter POs by.')
            ->optional();

        $schema->integer('payment_term_id')
            ->description('Optional payment term ID to filter POs by.')
            ->optional();

        $schema->string('status')
            ->description('Optional status code (e.g., PEND, APRV, RJCT, VOID).')
            ->optional();

        $schema->string('received_status')
            ->description('Optional received status (e.g., NONE, PART, COMP).')
            ->optional();

        $schema->string('date_from')
            ->description('Optional start date (YYYY-MM-DD) for PO creation.')
            ->optional();

        $schema->string('date_to')
            ->description('Optional end date (YYYY-MM-DD) for PO creation.')
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
        $contractItemId = isset($arguments['contract_item_id']) && $arguments['contract_item_id'] !== '' ? (int) $arguments['contract_item_id'] : null;
        $componentId = isset($arguments['component_id']) && $arguments['component_id'] !== '' ? (int) $arguments['component_id'] : null;
        $requestId = isset($arguments['material_quantity_request_id']) && $arguments['material_quantity_request_id'] !== '' ? (int) $arguments['material_quantity_request_id'] : null;
        $supplierId = isset($arguments['supplier_id']) && $arguments['supplier_id'] !== '' ? (int) $arguments['supplier_id'] : null;
        $paymentTermId = isset($arguments['payment_term_id']) && $arguments['payment_term_id'] !== '' ? (int) $arguments['payment_term_id'] : null;
        $status = isset($arguments['status']) ? trim((string) $arguments['status']) : '';
        $receivedStatus = isset($arguments['received_status']) ? trim((string) $arguments['received_status']) : '';
        $dateFrom = ! empty($arguments['date_from']) ? trim((string) $arguments['date_from']) : null;
        $dateTo = ! empty($arguments['date_to']) ? trim((string) $arguments['date_to']) : null;

        $page = max(1, (int) ($arguments['page'] ?? 1));
        $limit = min(100, max(1, (int) ($arguments['limit'] ?? 15)));
        $orderBy = (string) ($arguments['order_by'] ?? 'id');
        $order = strtoupper((string) ($arguments['order'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $modelInstance = new PurchaseOrder();
        $table = $modelInstance->getTable();
        $schema = $modelInstance->getConnection()->getSchemaBuilder();
        if (! $schema->hasColumn($table, $orderBy)) {
            $orderBy = 'id';
        }

        $query = PurchaseOrder::query();

        if (! is_null($id)) {
            $query->where('id', $id);
        }

        if (! is_null($projectId)) {
            $query->where('project_id', $projectId);
        }

        if (! is_null($sectionId)) {
            $query->where('section_id', $sectionId);
        }

        if (! is_null($contractItemId)) {
            $query->where('contract_item_id', $contractItemId);
        }

        if (! is_null($componentId)) {
            $query->where('component_id', $componentId);
        }

        if (! is_null($requestId)) {
            $query->where('material_quantity_request_id', $requestId);
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

        if ($receivedStatus !== '' && $schema->hasColumn($table, 'received_status')) {
            $query->where('received_status', $receivedStatus);
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
            'model' => 'purchase_order',
            'filters_applied' => array_filter([
                'id' => $id,
                'project_id' => $projectId,
                'section_id' => $sectionId,
                'contract_item_id' => $contractItemId,
                'component_id' => $componentId,
                'material_quantity_request_id' => $requestId,
                'supplier_id' => $supplierId,
                'payment_term_id' => $paymentTermId,
                'status' => $status !== '' ? $status : null,
                'received_status' => $receivedStatus !== '' ? $receivedStatus : null,
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
