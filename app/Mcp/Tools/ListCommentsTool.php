<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\McpModelRegistry;
use App\Models\Comment;
use Generator;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('List Comments')]
class ListCommentsTool extends Tool
{
    use HasDynamicDescription;

    public function name(): string
    {
        return 'list_comments';
    }

    public function defaultDescription(): string
    {
        return 'List and filter system comments and audit logs. Supports filtering by comment ID, comment type (e.g. char(4)), record type (e.g. PROJEC, PRORDR), record ID, content (partial match), author/creator, and creation date range.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        $schema->integer('id')
            ->description('Optional comment ID to match exactly.')
            ->optional();

        $schema->string('comment_type')
            ->description('Optional comment type code.')
            ->optional();

        $schema->string('record_type')
            ->description('Optional entity record type (e.g., PROJEC, PRORDR).')
            ->optional();

        $schema->integer('record_id')
            ->description('Optional entity record ID the comment belongs to.')
            ->optional();

        $schema->string('content')
            ->description('Optional comment content or keyword to search (partial match).')
            ->optional();

        $schema->integer('created_by')
            ->description('Optional user ID who wrote the comment.')
            ->optional();

        $schema->string('date_from')
            ->description('Optional start date (YYYY-MM-DD) for comment creation.')
            ->optional();

        $schema->string('date_to')
            ->description('Optional end date (YYYY-MM-DD) for comment creation.')
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
        $commentType = isset($arguments['comment_type']) ? trim((string) $arguments['comment_type']) : '';
        $recordType = isset($arguments['record_type']) ? trim((string) $arguments['record_type']) : '';
        $recordId = isset($arguments['record_id']) && $arguments['record_id'] !== '' ? (int) $arguments['record_id'] : null;
        $content = isset($arguments['content']) ? trim((string) $arguments['content']) : '';
        $createdBy = isset($arguments['created_by']) && $arguments['created_by'] !== '' ? (int) $arguments['created_by'] : null;
        $dateFrom = ! empty($arguments['date_from']) ? trim((string) $arguments['date_from']) : null;
        $dateTo = ! empty($arguments['date_to']) ? trim((string) $arguments['date_to']) : null;

        $page = max(1, (int) ($arguments['page'] ?? 1));
        $limit = min(100, max(1, (int) ($arguments['limit'] ?? 15)));
        $orderBy = (string) ($arguments['order_by'] ?? 'id');
        $order = strtoupper((string) ($arguments['order'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $modelInstance = new Comment();
        $table = $modelInstance->getTable();
        $schema = $modelInstance->getConnection()->getSchemaBuilder();
        if (! $schema->hasColumn($table, $orderBy)) {
            $orderBy = 'id';
        }

        $query = Comment::query();

        if (! is_null($id)) {
            $query->where('id', $id);
        }

        if ($commentType !== '' && $schema->hasColumn($table, 'comment_type')) {
            $query->where('comment_type', $commentType);
        }

        if ($recordType !== '' && $schema->hasColumn($table, 'record_type')) {
            $query->where('record_type', $recordType);
        }

        if (! is_null($recordId) && $schema->hasColumn($table, 'record_id')) {
            $query->where('record_id', $recordId);
        }

        if ($content !== '') {
            $query->where('content', 'LIKE', '%' . $content . '%');
        }

        if (! is_null($createdBy) && $schema->hasColumn($table, 'created_by')) {
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
            'model' => 'comment',
            'filters_applied' => array_filter([
                'id' => $id,
                'comment_type' => $commentType !== '' ? $commentType : null,
                'record_type' => $recordType !== '' ? $recordType : null,
                'record_id' => $recordId,
                'content' => $content !== '' ? $content : null,
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
