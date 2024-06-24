<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\RoleAccessCode;
use Illuminate\Support\Facades\DB;

class Role extends Model
{
    use HasFactory;

    public $deleteException = null;
    
    protected $table = 'roles';

    public function AccessCodeList(): HasMany
    {
        return $this->hasMany(RoleAccessCode::class);
    }

    public function delete(){
        
        DB::beginTransaction();
    
        try {  
            //Section
            $roleAccessCodes = $this->AccessCodeList;

            if($roleAccessCodes){

                foreach($roleAccessCodes as $roleAccessCode){
                    
                    if(!$roleAccessCode->delete()){
                        throw new Exception('Unable to delete role access code ID: '.$roleAccessCode->id);
                    }

                }//foreach
    
            }//if
            
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
