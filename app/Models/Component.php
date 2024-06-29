<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Section;
use App\Models\ComponentItem;
use App\Models\User;

class Component extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'components';

    public $deleteException = null;

  
    public function Section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function ComponentItems(): HasMany
    {
        return $this->hasMany(ComponentItem::class);
    }

    public function CreatedByUser(){   
        return User::find($this->created_by);
    }

    public function UpdatedByUser(){   
        $user = User::find($this->updated_by);

        if(!$user){
            return (object) (new User())->getAttributes();
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
    
}
