<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\MaterialItem;
use App\Models\Supplier;
use Generator;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('Get Material Price Stats')]
class GetMaterialPriceStatsTool extends Tool
{
    public function name(): string
    {
        return 'get_material_price_stats';
    }

    public function description(): string
    {
        return 'Calculates average, minimum, maximum, and latest price statistics for a material item based on approved canvasses (both standard and direct canvasses). Supports optional filtering by supplier_id and date range (date_from and date_to based on approval date).';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        $schema->integer('material_item_id')
            ->description('The ID of the material item.')
            ->required();

        $schema->integer('supplier_id')
            ->description('Optional ID of the supplier to filter canvasses by.');

        $schema->string('date_from')
            ->description('Optional start date (YYYY-MM-DD) to filter canvasses approved on or after this date.');

        $schema->string('date_to')
            ->description('Optional end date (YYYY-MM-DD) to filter canvasses approved on or before this date.');

        return $schema;
    }

    public function handle(array $arguments): ToolResult|Generator
    {
        $materialItemId = isset($arguments['material_item_id']) ? (int) $arguments['material_item_id'] : null;
        $supplierId = isset($arguments['supplier_id']) && $arguments['supplier_id'] !== '' ? (int) $arguments['supplier_id'] : null;
        $dateFrom = ! empty($arguments['date_from']) ? (string) $arguments['date_from'] : null;
        $dateTo = ! empty($arguments['date_to']) ? (string) $arguments['date_to'] : null;

        if (! $materialItemId) {
            return ToolResult::error('material_item_id is required.');
        }

        $materialItem = MaterialItem::withTrashed()->find($materialItemId);
        if (! $materialItem) {
            return ToolResult::error("Material item with ID {$materialItemId} not found.");
        }

        // Standard Material Canvasses query
        $mcQuery = DB::table('material_canvass')
            ->join('material_quantity_request_items', 'material_canvass.material_quantity_request_item_id', '=', 'material_quantity_request_items.id')
            ->whereNull('material_canvass.deleted_at')
            ->where('material_canvass.status', 'APRV')
            ->where('material_quantity_request_items.material_item_id', $materialItemId)
            ->select([
                'material_canvass.id as canvass_id',
                'material_canvass.price',
                'material_canvass.supplier_id',
                'material_canvass.approved_at',
                DB::raw("'standard' as source"),
            ]);

        // Direct Material Canvasses query
        $dmcQuery = DB::table('direct_material_canvass')
            ->join('direct_material_quantity_request_items', 'direct_material_canvass.direct_material_quantity_request_item_id', '=', 'direct_material_quantity_request_items.id')
            ->whereNull('direct_material_canvass.deleted_at')
            ->where('direct_material_canvass.status', 'APRV')
            ->where('direct_material_quantity_request_items.material_item_id', $materialItemId)
            ->select([
                'direct_material_canvass.id as canvass_id',
                'direct_material_canvass.price',
                'direct_material_canvass.supplier_id',
                'direct_material_canvass.approved_at',
                DB::raw("'direct' as source"),
            ]);

        if ($supplierId) {
            $mcQuery->where('material_canvass.supplier_id', $supplierId);
            $dmcQuery->where('direct_material_canvass.supplier_id', $supplierId);
        }

        if ($dateFrom) {
            $mcQuery->where('material_canvass.approved_at', '>=', $dateFrom . ' 00:00:00');
            $dmcQuery->where('direct_material_canvass.approved_at', '>=', $dateFrom . ' 00:00:00');
        }

        if ($dateTo) {
            $mcQuery->where('material_canvass.approved_at', '<=', $dateTo . ' 23:59:59');
            $dmcQuery->where('direct_material_canvass.approved_at', '<=', $dateTo . ' 23:59:59');
        }

        $records = $mcQuery->unionAll($dmcQuery)->get();
        $totalCount = $records->count();

        $meta = [
            'material_item_id' => $materialItemId,
            'material_name' => $materialItem->formatted_name ?? $materialItem->name,
            'filters_applied' => [
                'supplier_id' => $supplierId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'total_approved_canvasses' => $totalCount,
        ];

        if ($totalCount === 0) {
            return ToolResult::json(array_merge($meta, [
                'stats' => null,
                'message' => 'No approved canvasses found matching the specified criteria.',
            ]));
        }

        $prices = $records->pluck('price')->map(fn ($p) => (float) $p);
        $minPrice = $prices->min();
        $maxPrice = $prices->max();
        $avgPrice = round($prices->avg(), 2);

        // Find latest approved canvass
        $latest = $records->sortByDesc('approved_at')->first();

        // Unique supplier names
        $supplierIds = $records->pluck('supplier_id')->filter()->unique()->all();
        $supplierNames = ! empty($supplierIds)
            ? Supplier::withTrashed()->whereIn('id', $supplierIds)->pluck('name', 'id')->all()
            : [];

        // Supplier breakdown
        $bySupplier = [];
        foreach ($records->groupBy('supplier_id') as $sId => $group) {
            $sPrices = $group->pluck('price')->map(fn ($p) => (float) $p);
            $sName = $supplierNames[$sId] ?? ('Supplier #' . $sId);
            $bySupplier[] = [
                'supplier_id' => (int) $sId,
                'supplier_name' => $sName,
                'canvass_count' => $group->count(),
                'min_price' => $sPrices->min(),
                'max_price' => $sPrices->max(),
                'average_price' => round($sPrices->avg(), 2),
            ];
        }

        // Sort suppliers by canvass count desc
        usort($bySupplier, fn ($a, $b) => $b['canvass_count'] <=> $a['canvass_count']);

        return ToolResult::json(array_merge($meta, [
            'stats' => [
                'average_price' => $avgPrice,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'latest_price' => $latest ? (float) $latest->price : null,
                'latest_approved_at' => $latest ? $latest->approved_at : null,
                'latest_source' => $latest ? $latest->source : null,
            ],
            'supplier_breakdown' => $bySupplier,
        ]));
    }
}
