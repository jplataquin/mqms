<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\McpModelRegistry;
use App\Models\Component;
use Generator;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('List Components')]
class ListComponentsTool extends Tool
{
    use HasDynamicDescription;

    public function name(): string
    {
        return 'list_components';
    }

    public function defaultDescription(): string
    {
        return 'List and filter components. Supports filtering by component ID, contract item ID, section ID, name (partial match), and status.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        $schema->integer('id')
            ->description('Optional component ID to match exactly.')
            ->optional();

        $schema->integer('contract_item_id')
            ->description('Optional contract item ID to filter components by.')
            ->optional();

        $schema->integer('section_id')
            ->description('Optional section ID to filter components by.')
            ->optional();

        $schema->string('name')
            ->description('Optional component name or keyword to search (partial match).')
            ->optional();

        $schema->string('status')
            ->description('Optional component status (e.g., ACTV, ARCH, PEND).')
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
        $sectionId = isset($arguments['section_id']) && $arguments['section_id'] !== '' ? (int) $arguments['section_id'] : null;
        $name = isset($arguments['name']) ? trim((string) $arguments['name']) : '';
        $status = isset($arguments['status']) ? trim((string) $arguments['status']) : '';

        $page = max(1, (int) ($arguments['page'] ?? 1));
        $limit = min(100, max(1, (int) ($arguments['limit'] ?? 15)));
        $orderBy = (string) ($arguments['order_by'] ?? 'id');
        $order = strtoupper((string) ($arguments['order'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $modelInstance = new Component();
        $table = $modelInstance->getTable();
        $schema = $modelInstance->getConnection()->getSchemaBuilder();
        if (! $schema->hasColumn($table, $orderBy)) {
            $orderBy = 'id';
        }

        $query = Component::query();

        if (! is_null($id)) {
            $query->where('id', $id);
        }

        if (! is_null($contractItemId)) {
            $query->where('contract_item_id', $contractItemId);
        }

        if (! is_null($sectionId)) {
            $query->where('section_id', $sectionId);
        }

        if ($name !== '') {
            $query->where('name', 'LIKE', '%' . $name . '%');
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
            'model' => 'component',
            'filters_applied' => array_filter([
                'id' => $id,
                'contract_item_id' => $contractItemId,
                'section_id' => $sectionId,
                'name' => $name !== '' ? $name : null,
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
