<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DirectMaterialQuantityRequestItem extends Model
{
    use HasFactory;

    protected $table = 'direct_material_quantity_request_items';

    public function Request(): BelongsTo
    {
        return $this->belongsTo(DirectMaterialQuantityRequest::class, 'direct_material_quantity_request_id');
    }

    public function MaterialItem(): BelongsTo
    {
        return $this->belongsTo(MaterialItem::class, 'material_item_id');
    }

    public function Canvasses(): HasMany
    {
        return $this->hasMany(DirectMaterialCanvass::class, 'direct_material_quantity_request_item_id');
    }
}
