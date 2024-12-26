<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Project;
use App\Models\Component;
use App\Models\ContractItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Section extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'sections';
    public $deleteException = null;

    public function Project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // public function materialBudget(): HasOne
    // {
    //     return $this->hasOne(MaterialBudget::class);
    // }

    public function Components(): HasMany
    {
        return $this->hasMany(Component::class);
    }

    public function ContractItems(): HasMany
    {
        return $this->hasMany(ContractItem::class);
    }

    public function delete(){
        DB::beginTransaction();

        try {  

            //MaterialBudget
            $contract_items = $this->ContractItems;
            
            if($contract_items){
                
                foreach($contract_items as $contract_item){
                    
                    if(!$contract_item->delete()){
                        throw new Exception($contract_item->deleteException);
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

    public function getGrandTotalAmounts(){

        $contract_grand_total_amount        = 0;
        $ref_1_grand_total_amount          = 0;
        $material_budget_grand_total_amount = 0;

        $contract_items = $this->ContractItems;

        foreach($contract_items as $contract_item){

            //Contract
            $contract_grand_total_amount = $contract_grand_total_amount + ($contract_item->contract_unit_price * $contract_item->contract_quantity);

            //Reference
            $ref_1_grand_total_amount = $ref_1_grand_total_amount + ($contract_item->ref_1_quantity * $contract_item->ref_1_unit_price);

            /********************************/
            $components = $contract_item->Components;

            foreach($components as $component){
                
                $material_budget_grand_total_amount = $material_budget_grand_total_amount + $component->getGrandTotalAmount();
                
            }
        }

        return [
            'contract'  => $contract_grand_total_amount,
            'reference' => $ref_1_grand_total_amount,
            'material'  => $material_budget_grand_total_amount
        ];
    }
}
