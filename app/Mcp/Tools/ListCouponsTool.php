<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\McpModelRegistry;
use App\Models\Coupon;
use Generator;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('List Coupons')]
class ListCouponsTool extends Tool
{
    public function name(): string
    {
        return 'list_coupons';
    }

    public function description(): string
    {
        return 'List and filter coupons/vouchers. Supports filtering by coupon ID, status (UNCL, CLMD, APRV, RJCT, VOID), plate number (partial match), code, claimed by name (partial match), remarks (partial match), creator, and creation date range.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        $schema->integer('id')
            ->description('Optional coupon ID to match exactly.')
            ->optional();

        $schema->string('status')
            ->description('Optional coupon status (e.g., UNCL, CLMD, APRV, RJCT, VOID).')
            ->optional();

        $schema->string('plate_no')
            ->description('Optional vehicle plate number or keyword to search (partial match).')
            ->optional();

        $schema->string('code')
            ->description('Optional coupon hash or code to search.')
            ->optional();

        $schema->string('claimed_by_name')
            ->description('Optional name of person who claimed the coupon (partial match).')
            ->optional();

        $schema->string('remarks')
            ->description('Optional remarks or keyword to search (partial match).')
            ->optional();

        $schema->integer('created_by')
            ->description('Optional user ID who created the coupon.')
            ->optional();

        $schema->string('date_from')
            ->description('Optional start date (YYYY-MM-DD) for coupon creation.')
            ->optional();

        $schema->string('date_to')
            ->description('Optional end date (YYYY-MM-DD) for coupon creation.')
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
        $status = isset($arguments['status']) ? trim((string) $arguments['status']) : '';
        $plateNo = isset($arguments['plate_no']) ? trim((string) $arguments['plate_no']) : '';
        $code = isset($arguments['code']) ? trim((string) $arguments['code']) : '';
        $claimedByName = isset($arguments['claimed_by_name']) ? trim((string) $arguments['claimed_by_name']) : '';
        $remarks = isset($arguments['remarks']) ? trim((string) $arguments['remarks']) : '';
        $createdBy = isset($arguments['created_by']) && $arguments['created_by'] !== '' ? (int) $arguments['created_by'] : null;
        $dateFrom = ! empty($arguments['date_from']) ? trim((string) $arguments['date_from']) : null;
        $dateTo = ! empty($arguments['date_to']) ? trim((string) $arguments['date_to']) : null;

        $page = max(1, (int) ($arguments['page'] ?? 1));
        $limit = min(100, max(1, (int) ($arguments['limit'] ?? 15)));
        $orderBy = (string) ($arguments['order_by'] ?? 'id');
        $order = strtoupper((string) ($arguments['order'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $modelInstance = new Coupon();
        $table = $modelInstance->getTable();
        $schema = $modelInstance->getConnection()->getSchemaBuilder();
        if (! $schema->hasColumn($table, $orderBy)) {
            $orderBy = 'id';
        }

        $query = Coupon::query();

        if (! is_null($id)) {
            $query->where('id', $id);
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($plateNo !== '' && $schema->hasColumn($table, 'plate_no')) {
            $query->where('plate_no', 'LIKE', '%' . $plateNo . '%');
        }

        if ($code !== '') {
            $query->where('code', 'LIKE', '%' . $code . '%');
        }

        if ($claimedByName !== '' && $schema->hasColumn($table, 'claimed_by_name')) {
            $query->where('claimed_by_name', 'LIKE', '%' . $claimedByName . '%');
        }

        if ($remarks !== '' && $schema->hasColumn($table, 'remarks')) {
            $query->where('remarks', 'LIKE', '%' . $remarks . '%');
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
            'model' => 'coupon',
            'filters_applied' => array_filter([
                'id' => $id,
                'status' => $status !== '' ? $status : null,
                'plate_no' => $plateNo !== '' ? $plateNo : null,
                'code' => $code !== '' ? $code : null,
                'claimed_by_name' => $claimedByName !== '' ? $claimedByName : null,
                'remarks' => $remarks !== '' ? $remarks : null,
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
