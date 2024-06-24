<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Component;
use App\Models\MaterialQuantity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ComponentItem extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'component_items';

    public $deleteException = null;

    public function MaterialQuantities(): HasMany
    {
        return $this->hasMany(MaterialQuantity::class);
    }

    public function Component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    public function delete(){

        DB::beginTransaction();

        try{

            $materialQuantities = $this->MaterialQuantities;
            
            foreach($materialQuantities as $materialQuantity){

                if(!$materialQuantity->delete()){
                    throw new Exception($materialQuantity->deleteException);
                }
            
            }

            $this->deleted_by = Auth::user()->id;
            $this->save();
            parent::delete();

            DB::commit();

            return true;

        }catch(\Exception $e){

            $this->deleteException = $e;

            DB::rollback();

            return false;
        } 


    }
}