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
}
