<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\MaterialQuantityRequestItem;
use App\Models\PurchaseOrder;
use App\Models\Project;
use App\Models\Section;
use App\Models\ContractItem;
use App\Models\Component;
use App\Models\ComponentItem;
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

    public function PurchaseOrder(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
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

    public function ContractItem(): BelongsTo
    {
        return $this->belongsTo(ContractItem::class);
    }


    public function User(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }


    public function CreatedByUser(){   

        $user = User::find($this->created_by);

        if(!$user){
            return User::defaultAttirbutes();
        }

        return $user;
    }

    public function UpdatedByUser(){   
       
        $user = User::find($this->updated_by);

        if(!$user){
            return User::defaultAttirbutes();
        }

        return $user;
    }

    public function ApprovedByUser(){   
       
        $user = User::find($this->approved_by);

        if(!$user){
            return User::defaultAttirbutes();
        }

        return $user;
    }

    public function RejectedByUser(){   
       
        $user = User::find($this->rejected_by);

        if(!$user){
            return User::defaultAttirbutes();
        }

        return $user;
    }

}
