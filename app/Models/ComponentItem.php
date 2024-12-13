<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Component;
use App\Models\Unit;
use App\Models\MaterialQuantity;
use App\Models\MaterialQuantityRequestItem;
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

    public function MaterialQuantityRequestItems(){
        return $this->hasMany(MaterialQuantityRequestItem::class);
    }

    public function ref_1_unit_text(){
       return $this->getUnitText($this->ref_1_unit_id);
    }

    public function unit_text(){
        return $this->getUnitText($this->unit_id);
    }

    public function function_type_text(){

        $text = '';

        switch($this->function_type_id){

            case 1:
                $text = 'As Factor';
                break;
            
            case 2:
                $text = 'As Divisor';
                break;

            case 3: 
                $text = 'As Direct';
                break;

            case 4:
                $text = 'As Equivalent';
                break;
        }

        return $text;
    }

    private function getUnitText($id){

        $unit = Unit::find($id);

        if(!$unit){
            return '';
        }
        
        $text = '';

        $text = $unit;

        if($unit->deleted_at){
            $text = $text.' [Deleted]';
        }

        return $text;
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