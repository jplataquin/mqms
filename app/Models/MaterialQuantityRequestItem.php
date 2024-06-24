<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\MaterialQuantityRequest;
use App\Models\MaterialCanvass;

class MaterialQuantityRequestItem extends Model
{
    use HasFactory;

    protected $table = 'material_quantity_request_items';
    
    public $deleteException = null;

    public function Request(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function MaterialCanvass(): HasMany
    {
        return $this->hasMany(MaterialCanvass::class);
    }
}
