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
}
