<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\McpModelRegistry;
use App\Models\Accomplishment;
use Generator;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('List Accomplishments')]
class ListAccomplishmentsTool extends Tool
{
    public function name(): string
    {
        return 'list_accomplishments';
    }

    public function description(): string
    {
        return 'List and filter accomplishment records. Supports filtering by accomplishment ID, contract item ID, type (ACTUAL/TARGET), entry date range, creator, remarks (partial match), and creation date range.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        $schema->integer('id')
            ->description('Optional accomplishment record ID to match exactly.')
            ->optional();

        $schema->integer('contract_item_id')
            ->description('Optional contract item ID to filter accomplishments by.')
            ->optional();

        $schema->string('type')
            ->description('Optional accomplishment type (e.g., ACTUAL, TARGET).')
            ->optional();

        $schema->string('entry_date_from')
            ->description('Optional start date (YYYY-MM-DD) for accomplishment entry date.')
            ->optional();

        $schema->string('entry_date_to')
            ->description('Optional end date (YYYY-MM-DD) for accomplishment entry date.')
            ->optional();

        $schema->string('remarks')
            ->description('Optional remarks or keyword to search (partial match).')
            ->optional();

        $schema->integer('created_by')
            ->description('Optional user ID who created the record.')
            ->optional();

        $schema->string('date_from')
            ->description('Optional start date (YYYY-MM-DD) for record creation.')
            ->optional();

        $schema->string('date_to')
            ->description('Optional end date (YYYY-MM-DD) for record creation.')
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
        $contractItemId = isset($arguments['contract_item_id']) && $arguments['contract_item_id'] !== '' ? (int) $arguments['contract_item_id'] : null;
        $type = isset($arguments['type']) ? trim((string) $arguments['type']) : '';
        $entryDateFrom = ! empty($arguments['entry_date_from']) ? trim((string) $arguments['entry_date_from']) : null;
        $entryDateTo = ! empty($arguments['entry_date_to']) ? trim((string) $arguments['entry_date_to']) : null;
        $remarks = isset($arguments['remarks']) ? trim((string) $arguments['remarks']) : '';
        $createdBy = isset($arguments['created_by']) && $arguments['created_by'] !== '' ? (int) $arguments['created_by'] : null;
        $dateFrom = ! empty($arguments['date_from']) ? trim((string) $arguments['date_from']) : null;
        $dateTo = ! empty($arguments['date_to']) ? trim((string) $arguments['date_to']) : null;

        $page = max(1, (int) ($arguments['page'] ?? 1));
        $limit = min(100, max(1, (int) ($arguments['limit'] ?? 15)));
        $orderBy = (string) ($arguments['order_by'] ?? 'id');
        $order = strtoupper((string) ($arguments['order'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $modelInstance = new Accomplishment();
        $table = $modelInstance->getTable();
        $schema = $modelInstance->getConnection()->getSchemaBuilder();
        if (! $schema->hasColumn($table, $orderBy)) {
            $orderBy = 'id';
        }

        $query = Accomplishment::query();

        if (! is_null($id)) {
            $query->where('id', $id);
        }

        if (! is_null($contractItemId) && $schema->hasColumn($table, 'contract_item_id')) {
            $query->where('contract_item_id', $contractItemId);
        }

        if ($type !== '') {
            $query->where('type', $type);
        }

        // Support both entry_data and legacy entry_date column names
        $entryDateCol = $schema->hasColumn($table, 'entry_data') ? 'entry_data' : ($schema->hasColumn($table, 'entry_date') ? 'entry_date' : null);
        if ($entryDateCol) {
            if ($entryDateFrom) {
                $query->where($entryDateCol, '>=', $entryDateFrom);
            }
            if ($entryDateTo) {
                $query->where($entryDateCol, '<=', $entryDateTo);
            }
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
            'model' => 'accomplishment',
            'filters_applied' => array_filter([
                'id' => $id,
                'contract_item_id' => $contractItemId,
                'type' => $type !== '' ? $type : null,
                'entry_date_from' => $entryDateFrom,
                'entry_date_to' => $entryDateTo,
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
