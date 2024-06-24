<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\MaterialQuantityRequestItem;
use App\Models\Project;
use App\Models\Section;
use App\Models\Component;
use App\Models\User;

class MaterialQuantityRequest extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'material_quantity_requests';
    
    public $deleteException = null;

    public function Items(): HasMany
    {
        return $this->hasMany(MaterialQuantityRequestItem::class);
    }

    public function Project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function Section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function Component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }


    public function User(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }
}
