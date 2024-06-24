<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\MaterialGroup;
use Illuminate\Support\Facades\Auth;

class MaterialItem extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'material_items';

    public function Group(): HasOne
    {
        return $this->hasOne(MaterialGroup::class);
    }

}
