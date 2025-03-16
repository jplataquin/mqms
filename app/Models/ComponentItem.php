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
    protected $appends = [
        'unit_text',
        'ref_1_unit_text',
        'amount',
        'ref_1_amount'
    ];

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

    public function getRef1UnitTextAttribute(){
       return $this->getUnitText($this->ref_1_unit_id);
    }

    public function getUnitTextAttribute(){
        return $this->getUnitText($this->unit_id);
    }

    public function getAmountAttribute(){
        
        $val = 0;

        $qty    = (float) $this->quantity;
        $price  = (float) $this->budget_price;

        $val = $qty * $price;

        return $val;
    }

    public function getRef1AmountAttribute(){
        
        $val = 0;

        $qty    = (float) $this->ref_1_quantity;
        $price  = (float) $this->ref_1_unit_price;

        $val = $qty * $price;

        return $val;
    }


    public function factorTextValue($use_count,$component_unit){


        //As Factor
        if($this->function_type_id == 1){
           
            $answer = $this->function_variable  / $use_count;
            $answer = round($answer,6);
            $answer = '(Fa) '.$answer.' '.$this->unit_text.'/'.$component_unit;
        }

        //As Divisor
        if($this->function_type_id == 2){
            $answer = (1 / $this->function_variable) / $use_count;
            $answer = round($answer,6);
            $answer = '(Dv) '.$answer.' '.$this->unit_text.'/'.$component_unit;
        }

        //As Direct
        if($this->function_type_id == 3){
           $answer = $this->function_variable;
           $answer = '(Dr) '.$answer.' '.$this->unit_text;
        }

        //As Equivalent
        if($this->function_type_id == 4){

            $answer = $this->function_variable * $use_count;
            $answer = round($answer,6);

            $answer = '(Eq) '.$answer.' '.$component_unit.'/'.$this->unit_text;
        }

        return $answer;
    }
    //--------old---------------------

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

        $text = $unit->text;

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

    public function getGrandTotalAmount(){
        return ($this->quantity * $this->budget_price);
    }
}