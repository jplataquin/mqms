<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\Section;
use App\Models\ComponentItem;
use App\Models\ContractItem;
use App\Models\User;
use App\Models\Unit;

class Component extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'components';
    protected $appends = array('unit_text');


    public $deleteException = null;

  
    public function Section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function ContractItem(): BelongsTo
    {
        return $this->belongsTo(ContractItem::class);
    }

    public function ComponentItems(): HasMany
    {
        return $this->hasMany(ComponentItem::class);
    }
    
    
    public function getUnitTextAttribute(){

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

    public function DeletedByUser(){   
       
        $user = User::find($this->deleted_by);

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

    public function delete(){

        DB::beginTransaction();

        try {  

            $componentItems = $this->ComponentItems;

            if($componentItems){

                foreach($componentItems as $componentItem){
                    
                    if(!$componentItem->delete()){
                        throw new Exception($componentItem->deleteException);
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
    
    public function getGrandTotalAmount(){

        $grand_total_amount = 0;

        $component_items = $this->ComponentItems;

        if($component_items){
            foreach($component_items as $component_item){
                $grand_total_amount = $grand_total_amount + $component_item->getGrandTotalAmount();
            }
        }

        return $grand_total_amount;
    }
}
