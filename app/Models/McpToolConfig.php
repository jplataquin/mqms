<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class McpToolConfig extends Model
{
    use HasFactory;

    protected $table = 'mcp_tool_configs';

    protected $fillable = [
        'tool_name',
        'custom_description',
        'updated_by',
    ];

    public function UpdatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the custom description for a tool name if one is configured.
     */
    public static function getCustomDescription(string $toolName): ?string
    {
        try {
            return static::where('tool_name', $toolName)->value('custom_description');
        } catch (\Throwable $e) {
            // Failsafe in case table is not yet migrated in an environment
            return null;
        }
    }
}
