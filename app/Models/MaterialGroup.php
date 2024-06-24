<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\MaterialItem;
use Illuminate\Support\Facades\Auth;

class MaterialGroup extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'material_groups';

    public function Items(): HasMany
    {
        return $this->hasMany(MaterialItem::class);
    }
}
