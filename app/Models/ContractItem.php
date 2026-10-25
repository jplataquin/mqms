<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Unit;
use App\Models\Section;
use App\Models\Component;
use App\Models\Accomplishment;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ContractItem extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'contract_items';
    protected $appends = [
        'contract_unit_text',
        'ref_1_unit_text',
        'budget_unit_text',
        'ref_1_amount',
        'contract_amount',
        'accomplished_amount',
        'budget_quantity_overwrite',
        'budget_total_amount_overwrite',
        'name',
        'latest_actual_accomplishment_text',
        'latest_actual_accomplishment_date'
    ];

    public $deleteException = null;

    
    // public function ParentContractItem(): BelongsTo
    // {
    //     return $this->belongsTo('ComponentItem','parent_contract_item_id','id');
    // }

    // public function SubContractItems(): HasMany
    // {
    //     return $this->hasMany('ContractItem','parent_contract_item_id','id');
    // }

    public function Section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function Components(): HasMany
    {
        return $this->hasMany(Component::class);
    }

    public function Accomplishments(): HasMany
    {
        return $this->hasMany(Accomplishment::class);
    }

    public function CreatedByUser(){   

        $user = User::find($this->created_by);

        if(!$user){
            return User::defaultAttirbutes();
        }

        return $user;
    }

    public function name(){
        return $this->item_code.' '.$this->description;
    }

    public function getNameAttribute(){
        return $this->item_code.' '.$this->description;
    }

    public function getBudgetTotalAmountOverwriteAttribute(){
        return ($this->budget_unit_price > 0);
    }

    public function getBudgetQuantityOverwriteAttribute(){
        return ($this->budget_quantity && $this->budget_unit_id);
    }

    public function getContractUnitTextAttribute(){

        $unit = Unit::find($this->unit_id);

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


    public function getContractAmountAttribute(){

        $val = 0;
 
        $qty     = (float) $this->contract_quantity;
        $price   = (float) $this->contract_unit_price;
 
        $val = $qty * $price;
 
        return $val;
    }

    public function latestActualAccomplishment()
    {
        return $this->Accomplishments()
            ->where('type', 'ACTUAL')
            ->orderBy('entry_data', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function getLatestActualAccomplishmentQuantityAttribute()
    {
        $latest = $this->latestActualAccomplishment();
        return $latest ? (float) $latest->quantity : 0.0;
    }

    public function getLatestActualAccomplishmentPercentageAttribute()
    {
        $qty = $this->latest_actual_accomplishment_quantity;
        $total = (float) $this->contract_quantity;
        return $total > 0 ? ($qty / $total) * 100 : 0.0;
    }

    public function getLatestActualAccomplishmentDateAttribute()
    {
        $latest = $this->latestActualAccomplishment();
        if (!$latest) {
            return null;
        }

        if ($latest->entry_data instanceof \DateTimeInterface) {
            return $latest->entry_data->format('Y-m-d');
        }

        if ($latest->entry_data) {
            return substr((string) $latest->entry_data, 0, 10);
        }

        if ($latest->created_at instanceof \DateTimeInterface) {
            return $latest->created_at->format('Y-m-d');
        }

        return null;
    }

    public function getLatestActualAccomplishmentTextAttribute()
    {
        $qty = $this->latest_actual_accomplishment_quantity;
        $unit = $this->contract_unit_text;
        $percentage = $this->latest_actual_accomplishment_percentage;
        $date = $this->latest_actual_accomplishment_date;

        $text = trim(number_format($qty, 2) . ' ' . $unit) . ' (' . number_format($percentage, 2) . '%)';

        if ($date) {
            $text .= ' as of ' . $date;
        }

        return $text;
    }

    public function getAccomplishedAmountAttribute(){

        $latest = $this->latestActualAccomplishment();

        if ($latest) {
            return (float) $latest->quantity * (float) $this->contract_unit_price;
        }

        return 0.0;
    }

    public function getRef1AmountAttribute(){

       $val = 0;

       $qty     = (float) $this->ref_1_quantity;
       $price   = (float) $this->ref_1_unit_price;

       $val = $qty * $price;

       return $val;
    }

    public function getRef1UnitTextAttribute(){

        $unit = Unit::find($this->ref_1_unit_id);

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

    public function getBudgetUnitTextAttribute(){

        $unit = Unit::find($this->budget_unit_id);

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

    public function UpdatedByUser(){   
       
        $user = User::find($this->updated_by);

        if(!$user){
            return User::defaultAttirbutes();
        }

        return $user;
    }

    public function DeletedByUser(){   
       
        $user = User::find($this->deleted_by);

        if(!$user){
            return User::defaultAttirbutes();
        }

        return $user;
    }


    public function delete(){

        DB::beginTransaction();

        try {  

            $components = $this->Components;

            if($components){

                foreach($components as $component){
                    
                    if(!$component->delete()){
                        throw new Exception($component->deleteException);
                    }
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

    public function getTotalAmount(){

        $amount = 0;

        $components = $this->Components;

        if($components){
            foreach($components as $component){

                //$amount = $amount + ($component->budget_price * $component->);
            }
        }
        
    }
}
