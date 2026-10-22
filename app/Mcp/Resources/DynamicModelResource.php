<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use App\Mcp\McpModelRegistry;
use Illuminate\Support\Str;
use Laravel\Mcp\Server\Resource;

class DynamicModelResource extends Resource
{
    protected string $modelKey;

    public function __construct(string $modelKey = 'project')
    {
        $this->modelKey = $modelKey;
    }

    public function name(): string
    {
        return $this->modelKey;
    }

    public function title(): string
    {
        return Str::headline($this->modelKey) . ' Model';
    }

    public function uri(): string
    {
        return 'mqms://models/' . $this->modelKey;
    }

    public function description(): string
    {
        return "Metadata and overview for {$this->title()} records. Access specific records at mqms://models/{$this->modelKey}/{id}.";
    }

    public function mimeType(): string
    {
        return 'application/json';
    }

    public function read(): string
    {
        $modelClass = McpModelRegistry::getModelClass($this->modelKey);
        if (! $modelClass) {
            return json_encode(['error' => 'Model not found']);
        }

        $instance = new $modelClass;
        $table = $instance->getTable();
        $schema = $instance->getConnection()->getSchemaBuilder();
        $columns = $schema->getColumnListing($table);

        // Filter out sensitive columns like password
        $safeColumns = array_values(array_diff($columns, ['password', 'remember_token', 'secret_key', 'api_key']));

        $total = $modelClass::count();

        return (string) json_encode([
            'model' => $this->modelKey,
            'table' => $table,
            'total_records' => $total,
            'columns' => $safeColumns,
            'access_uri_template' => "mqms://models/{$this->modelKey}/{id}",
        ], JSON_PRETTY_PRINT);
    }
}
