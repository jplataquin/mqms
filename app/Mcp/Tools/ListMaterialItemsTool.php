<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\McpModelRegistry;
use App\Models\MaterialItem;
use Generator;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('List Material Items')]
class ListMaterialItemsTool extends Tool
{
    use HasDynamicDescription;

    public function name(): string
    {
        return 'list_material_items';
    }

    public function defaultDescription(): string
    {
        return 'List and filter material items. Supports filtering by ID, material group ID, name (partial match), brand (partial match), and specification/packaging (partial match).';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        $schema->integer('id')
            ->description('Optional material item ID to match exactly.')
            ->optional();

        $schema->integer('material_group_id')
            ->description('Optional material group ID to filter items by.')
            ->optional();

        $schema->string('name')
            ->description('Optional material name or keyword to search (partial match).')
            ->optional();

        $schema->string('brand')
            ->description('Optional brand name or keyword to search (partial match).')
            ->optional();

        $schema->string('specification_unit_packaging')
            ->description('Optional specification/packaging keyword to search (partial match).')
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
        $materialGroupId = isset($arguments['material_group_id']) && $arguments['material_group_id'] !== '' ? (int) $arguments['material_group_id'] : null;
        $name = isset($arguments['name']) ? trim((string) $arguments['name']) : '';
        $brand = isset($arguments['brand']) ? trim((string) $arguments['brand']) : '';
        $specification = isset($arguments['specification_unit_packaging']) ? trim((string) $arguments['specification_unit_packaging']) : '';

        $page = max(1, (int) ($arguments['page'] ?? 1));
        $limit = min(100, max(1, (int) ($arguments['limit'] ?? 15)));
        $orderBy = (string) ($arguments['order_by'] ?? 'id');
        $order = strtoupper((string) ($arguments['order'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $modelInstance = new MaterialItem();
        $table = $modelInstance->getTable();
        $schema = $modelInstance->getConnection()->getSchemaBuilder();
        if (! $schema->hasColumn($table, $orderBy)) {
            $orderBy = 'id';
        }

        $query = MaterialItem::query();

        if (! is_null($id)) {
            $query->where('id', $id);
        }

        if (! is_null($materialGroupId)) {
            $query->where('material_group_id', $materialGroupId);
        }

        if ($name !== '') {
            $query->where('name', 'LIKE', '%' . $name . '%');
        }

        if ($brand !== '') {
            $query->where('brand', 'LIKE', '%' . $brand . '%');
        }

        if ($specification !== '') {
            $query->where('specification_unit_packaging', 'LIKE', '%' . $specification . '%');
        }

        $total = $query->count();
        $records = $query->orderBy($orderBy, $order)
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $records->map(fn ($r) => McpModelRegistry::sanitize($r))->toArray();

        return ToolResult::json([
            'model' => 'material_item',
            'filters_applied' => array_filter([
                'id' => $id,
                'material_group_id' => $materialGroupId,
                'name' => $name !== '' ? $name : null,
                'brand' => $brand !== '' ? $brand : null,
                'specification_unit_packaging' => $specification !== '' ? $specification : null,
            ], fn ($v) => ! is_null($v)),
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit),
            'records' => $data,
        ]);
    }
}
