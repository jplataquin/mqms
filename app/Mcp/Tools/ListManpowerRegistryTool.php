<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\McpModelRegistry;
use App\Models\ManpowerRegistry;
use Generator;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('List Manpower Registry')]
class ListManpowerRegistryTool extends Tool
{
    public function name(): string
    {
        return 'list_manpower_registry';
    }

    public function description(): string
    {
        return 'List and filter manpower registry records. Supports filtering by worker ID, status (PEND/ACTV), worker type (Labor/Skilled), structure category (VERT/HORI/BOTH), name (first, middle, last), gender, geographic region, province, city/municipality, skills (mason, welder, painter, electrician, carpenter, roofer, tile_setter, metal_worker), and creation date range.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        $schema->integer('id')
            ->description('Optional manpower worker ID to match exactly.')
            ->optional();

        $schema->string('status')
            ->description('Optional status code (e.g., PEND, ACTV).')
            ->optional();

        $schema->string('type')
            ->description('Optional worker type (e.g., Labor, Skilled).')
            ->optional();

        $schema->string('structure_category')
            ->description('Optional structure category (e.g., VERT, HORI, BOTH).')
            ->optional();

        $schema->string('name')
            ->description('Optional name keyword to search across firstname, middlename, or lastname (partial match).')
            ->optional();

        $schema->string('gender')
            ->description('Optional gender (e.g., M or F).')
            ->optional();

        $schema->string('region')
            ->description('Optional region code (e.g., VI____).')
            ->optional();

        $schema->string('province')
            ->description('Optional province code (e.g., ILOILO, AKLAN_).')
            ->optional();

        $schema->string('city_municipality')
            ->description('Optional city/municipality code.')
            ->optional();

        $schema->boolean('mason')
            ->description('Filter workers who have mason skill.')
            ->optional();

        $schema->boolean('welder')
            ->description('Filter workers who have welder skill.')
            ->optional();

        $schema->boolean('painter')
            ->description('Filter workers who have painter skill.')
            ->optional();

        $schema->boolean('electrician')
            ->description('Filter workers who have electrician skill.')
            ->optional();

        $schema->boolean('carpenter')
            ->description('Filter workers who have carpenter skill.')
            ->optional();

        $schema->boolean('roofer')
            ->description('Filter workers who have roofer skill.')
            ->optional();

        $schema->boolean('tile_setter')
            ->description('Filter workers who have tile setter skill.')
            ->optional();

        $schema->boolean('metal_worker')
            ->description('Filter workers who have metal worker skill.')
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
        $status = isset($arguments['status']) ? trim((string) $arguments['status']) : '';
        $type = isset($arguments['type']) ? trim((string) $arguments['type']) : '';
        $structureCategory = isset($arguments['structure_category']) ? trim((string) $arguments['structure_category']) : '';
        $name = isset($arguments['name']) ? trim((string) $arguments['name']) : '';
        $gender = isset($arguments['gender']) ? trim((string) $arguments['gender']) : '';
        $region = isset($arguments['region']) ? trim((string) $arguments['region']) : '';
        $province = isset($arguments['province']) ? trim((string) $arguments['province']) : '';
        $cityMunicipality = isset($arguments['city_municipality']) ? trim((string) $arguments['city_municipality']) : '';

        $mason = isset($arguments['mason']) ? (bool) $arguments['mason'] : null;
        $welder = isset($arguments['welder']) ? (bool) $arguments['welder'] : null;
        $painter = isset($arguments['painter']) ? (bool) $arguments['painter'] : null;
        $electrician = isset($arguments['electrician']) ? (bool) $arguments['electrician'] : null;
        $carpenter = isset($arguments['carpenter']) ? (bool) $arguments['carpenter'] : null;
        $roofer = isset($arguments['roofer']) ? (bool) $arguments['roofer'] : null;
        $tileSetter = isset($arguments['tile_setter']) ? (bool) $arguments['tile_setter'] : null;
        $metalWorker = isset($arguments['metal_worker']) ? (bool) $arguments['metal_worker'] : null;

        $dateFrom = ! empty($arguments['date_from']) ? trim((string) $arguments['date_from']) : null;
        $dateTo = ! empty($arguments['date_to']) ? trim((string) $arguments['date_to']) : null;

        $page = max(1, (int) ($arguments['page'] ?? 1));
        $limit = min(100, max(1, (int) ($arguments['limit'] ?? 15)));
        $orderBy = (string) ($arguments['order_by'] ?? 'id');
        $order = strtoupper((string) ($arguments['order'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $modelInstance = new ManpowerRegistry();
        $table = $modelInstance->getTable();
        $schema = $modelInstance->getConnection()->getSchemaBuilder();
        if (! $schema->hasColumn($table, $orderBy)) {
            $orderBy = 'id';
        }

        $query = ManpowerRegistry::query();

        if (! is_null($id)) {
            $query->where('id', $id);
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($type !== '') {
            $query->where('type', $type);
        }

        if ($structureCategory !== '') {
            $query->where('structure_category', $structureCategory);
        }

        if ($name !== '') {
            $query->where(function ($q) use ($name) {
                $q->where('firstname', 'LIKE', '%' . $name . '%')
                    ->orWhere('middlename', 'LIKE', '%' . $name . '%')
                    ->orWhere('lastname', 'LIKE', '%' . $name . '%');
            });
        }

        if ($gender !== '') {
            $query->where('gender', $gender);
        }

        if ($region !== '') {
            $query->where('region', $region);
        }

        if ($province !== '') {
            $query->where('province', $province);
        }

        if ($cityMunicipality !== '') {
            $query->where('city_municipality', $cityMunicipality);
        }

        $skillFields = [
            'mason' => $mason,
            'welder' => $welder,
            'painter' => $painter,
            'electrician' => $electrician,
            'carpenter' => $carpenter,
            'roofer' => $roofer,
            'tile_setter' => $tileSetter,
            'metal_worker' => $metalWorker,
        ];

        foreach ($skillFields as $field => $val) {
            if (! is_null($val) && $schema->hasColumn($table, $field)) {
                $query->where($field, $val ? 1 : 0);
            }
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
            'model' => 'manpower_registry',
            'filters_applied' => array_filter([
                'id' => $id,
                'status' => $status !== '' ? $status : null,
                'type' => $type !== '' ? $type : null,
                'structure_category' => $structureCategory !== '' ? $structureCategory : null,
                'name' => $name !== '' ? $name : null,
                'gender' => $gender !== '' ? $gender : null,
                'region' => $region !== '' ? $region : null,
                'province' => $province !== '' ? $province : null,
                'city_municipality' => $cityMunicipality !== '' ? $cityMunicipality : null,
                'mason' => $mason,
                'welder' => $welder,
                'painter' => $painter,
                'electrician' => $electrician,
                'carpenter' => $carpenter,
                'roofer' => $roofer,
                'tile_setter' => $tileSetter,
                'metal_worker' => $metalWorker,
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
