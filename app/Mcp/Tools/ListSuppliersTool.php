<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\McpModelRegistry;
use App\Models\Supplier;
use Generator;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('List Suppliers')]
class ListSuppliersTool extends Tool
{
    use HasDynamicDescription;

    public function name(): string
    {
        return 'list_suppliers';
    }

    public function defaultDescription(): string
    {
        return 'List and filter suppliers. Supports filtering by supplier ID, name (partial match), contact person (partial match), contact number (partial match), email (partial match), address (partial match), creator, and creation date range.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        $schema->integer('id')
            ->description('Optional supplier ID to match exactly.')
            ->optional();

        $schema->string('name')
            ->description('Optional supplier name or keyword to search (partial match).')
            ->optional();

        $schema->string('contact_person')
            ->description('Optional contact person name to search in primary or secondary contact person (partial match).')
            ->optional();

        $schema->string('contact_no')
            ->description('Optional contact number to search in primary or secondary contact number (partial match).')
            ->optional();

        $schema->string('email')
            ->description('Optional email address to search in primary or secondary email (partial match).')
            ->optional();

        $schema->string('address')
            ->description('Optional address keyword to search (partial match).')
            ->optional();

        $schema->integer('created_by')
            ->description('Optional user ID who created the supplier record.')
            ->optional();

        $schema->string('date_from')
            ->description('Optional start date (YYYY-MM-DD) for supplier creation.')
            ->optional();

        $schema->string('date_to')
            ->description('Optional end date (YYYY-MM-DD) for supplier creation.')
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
        $name = isset($arguments['name']) ? trim((string) $arguments['name']) : '';
        $contactPerson = isset($arguments['contact_person']) ? trim((string) $arguments['contact_person']) : '';
        $contactNo = isset($arguments['contact_no']) ? trim((string) $arguments['contact_no']) : '';
        $email = isset($arguments['email']) ? trim((string) $arguments['email']) : '';
        $address = isset($arguments['address']) ? trim((string) $arguments['address']) : '';
        $createdBy = isset($arguments['created_by']) && $arguments['created_by'] !== '' ? (int) $arguments['created_by'] : null;
        $dateFrom = ! empty($arguments['date_from']) ? trim((string) $arguments['date_from']) : null;
        $dateTo = ! empty($arguments['date_to']) ? trim((string) $arguments['date_to']) : null;

        $page = max(1, (int) ($arguments['page'] ?? 1));
        $limit = min(100, max(1, (int) ($arguments['limit'] ?? 15)));
        $orderBy = (string) ($arguments['order_by'] ?? 'id');
        $order = strtoupper((string) ($arguments['order'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $modelInstance = new Supplier();
        $table = $modelInstance->getTable();
        $schema = $modelInstance->getConnection()->getSchemaBuilder();
        if (! $schema->hasColumn($table, $orderBy)) {
            $orderBy = 'id';
        }

        $query = Supplier::query();

        if (! is_null($id)) {
            $query->where('id', $id);
        }

        if ($name !== '') {
            $query->where('name', 'LIKE', '%' . $name . '%');
        }

        if ($contactPerson !== '') {
            $query->where(function ($q) use ($contactPerson) {
                $q->where('primary_contact_person', 'LIKE', '%' . $contactPerson . '%')
                    ->orWhere('secondary_contact_person', 'LIKE', '%' . $contactPerson . '%');
            });
        }

        if ($contactNo !== '') {
            $query->where(function ($q) use ($contactNo) {
                $q->where('primary_contact_no', 'LIKE', '%' . $contactNo . '%')
                    ->orWhere('secondary_contact_no', 'LIKE', '%' . $contactNo . '%');
            });
        }

        if ($email !== '') {
            $query->where(function ($q) use ($email) {
                $q->where('primary_email', 'LIKE', '%' . $email . '%')
                    ->orWhere('secondary_email', 'LIKE', '%' . $email . '%');
            });
        }

        if ($address !== '') {
            $query->where('address', 'LIKE', '%' . $address . '%');
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
            'model' => 'supplier',
            'filters_applied' => array_filter([
                'id' => $id,
                'name' => $name !== '' ? $name : null,
                'contact_person' => $contactPerson !== '' ? $contactPerson : null,
                'contact_no' => $contactNo !== '' ? $contactNo : null,
                'email' => $email !== '' ? $email : null,
                'address' => $address !== '' ? $address : null,
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
