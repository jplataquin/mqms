<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\McpModelRegistry;
use App\Models\ContractItem;
use Generator;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('List Contract Items')]
class ListContractItemsTool extends Tool
{
    public function name(): string
    {
        return 'list_contract_items';
    }

    public function description(): string
    {
        return 'List and filter contract items. Supports filtering by contract item ID, section ID, item code (partial match), and description (partial match).';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        $schema->integer('id')
            ->description('Optional contract item ID to match exactly.')
            ->optional();

        $schema->integer('section_id')
            ->description('Optional section ID to filter contract items by.')
            ->optional();

        $schema->string('item_code')
            ->description('Optional item code or keyword to search (partial match).')
            ->optional();

        $schema->string('description')
            ->description('Optional description keyword to search (partial match).')
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
        $sectionId = isset($arguments['section_id']) && $arguments['section_id'] !== '' ? (int) $arguments['section_id'] : null;
        $itemCode = isset($arguments['item_code']) ? trim((string) $arguments['item_code']) : '';
        $description = isset($arguments['description']) ? trim((string) $arguments['description']) : '';

        $page = max(1, (int) ($arguments['page'] ?? 1));
        $limit = min(100, max(1, (int) ($arguments['limit'] ?? 15)));
        $orderBy = (string) ($arguments['order_by'] ?? 'id');
        $order = strtoupper((string) ($arguments['order'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $modelInstance = new ContractItem();
        $table = $modelInstance->getTable();
        $schema = $modelInstance->getConnection()->getSchemaBuilder();
        if (! $schema->hasColumn($table, $orderBy)) {
            $orderBy = 'id';
        }

        $query = ContractItem::query();

        if (! is_null($id)) {
            $query->where('id', $id);
        }

        if (! is_null($sectionId)) {
            $query->where('section_id', $sectionId);
        }

        if ($itemCode !== '') {
            $query->where('item_code', 'LIKE', '%' . $itemCode . '%');
        }

        if ($description !== '') {
            $query->where('description', 'LIKE', '%' . $description . '%');
        }

        $total = $query->count();
        $records = $query->orderBy($orderBy, $order)
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $records->map(fn ($r) => McpModelRegistry::sanitize($r))->toArray();

        return ToolResult::json([
            'model' => 'contract_item',
            'filters_applied' => array_filter([
                'id' => $id,
                'section_id' => $sectionId,
                'item_code' => $itemCode !== '' ? $itemCode : null,
                'description' => $description !== '' ? $description : null,
            ], fn ($v) => ! is_null($v)),
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit),
            'records' => $data,
        ]);
    }
}
