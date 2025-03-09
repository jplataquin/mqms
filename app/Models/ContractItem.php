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
use Illuminate\Database\Eloquent\Casts\Attribute;

class ContractItem extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'contract_items';
    protected $appends = array('contract_unit_text');

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
