<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DirectMaterialQuantityRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'direct_material_quantity_requests';

    public function Project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function Section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function Items(): HasMany
    {
        return $this->hasMany(DirectMaterialQuantityRequestItem::class, 'direct_material_quantity_request_id');
    }

    public function Canvasses(): HasMany
    {
        return $this->hasMany(DirectMaterialCanvass::class, 'direct_material_quantity_request_id');
    }

    public function Creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function Approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function Rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function Voider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'void_by');
    }

    public function getFormattedIdAttribute(): string
    {
        return str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    public function getHashCode(): string
    {
        $item_arr = [];

        foreach ($this->Items as $item) {
            $material_item = $item->MaterialItem;
            $mat_name = $material_item ? $material_item->formattedName : '';

            $item_arr[] = implode('-', [
                $item->id,
                $item->material_item_id,
                $mat_name,
                $item->quantity,
                $item->remarks ?? ''
            ]);
        }

        $project = $this->Project;
        $section = $this->Section;

        $header_arr = [
            $this->id,
            $project ? $project->name : '',
            $project ? $project->status : '',
            $section ? $section->name : '',
            $this->created_by,
            $this->created_at,
            $this->status,
            $this->description ?? ''
        ];

        $item_str   = implode(':', $item_arr);
        $header_str = implode('|', $header_arr);

        $app_key = config('app.key') ?: env('APP_KEY', '');
        $secret_text = $header_str . '=' . $item_str . '=' . $app_key;

        $raw_hash = hash('sha256', $secret_text);

        $hash = substr($raw_hash, 0, 3) . substr($raw_hash, 61, 3);

        return strtoupper($hash);
    }
}
