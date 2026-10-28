<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Section;
use App\Models\User;

class Project extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'projects';
    public $deleteException = null;

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'calendar_days' => 'integer',
    ];

    public function Sections(): HasMany
    {
        return $this->hasMany(Section::class);
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

    /**
     * Get the total contract amount (sum of all contract items) for the project.
     *
     * @param bool $onlyMonitored Whether to include only active, non-excluded contract items
     * @return float
     */
    public function getTotalContractAmount(bool $onlyMonitored = false): float
    {
        $total = 0;
        $sections = $this->relationLoaded('Sections') ? $this->Sections : $this->Sections()->with('ContractItems')->get();
        foreach ($sections as $section) {
            if (method_exists($section, 'trashed') && $section->trashed()) {
                continue;
            }
            $items = $section->relationLoaded('ContractItems') ? $section->ContractItems : $section->ContractItems()->get();
            foreach ($items as $ci) {
                if (method_exists($ci, 'trashed') && $ci->trashed()) {
                    continue;
                }
                if ($onlyMonitored && (int)$ci->exclude_from_monitoring === 1) {
                    continue;
                }
                $total += ((float) $ci->contract_quantity * (float) $ci->contract_unit_price);
            }
        }
        return (float) $total;
    }



    public function delete(){
        
        DB::beginTransaction();
    
        try {  
            //Section
            $sections = $this->Sections;

            if($sections){

                foreach($sections as $section){
                    
                    if(!$section->delete()){
                        throw new Exception($section->deleteException);
                    }

                }//foreach
    
            }//if
            
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
