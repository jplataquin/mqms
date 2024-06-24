<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Section;

class Project extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'projects';
    public $deleteException = null;

    public function Sections(): HasMany
    {
        return $this->hasMany(Section::class);
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
