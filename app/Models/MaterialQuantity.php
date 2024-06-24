<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\ComponentItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MaterialQuantity extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'material_quantities';
    
    public $deleteException = null;

    public function ComponentItem(): BelongsTo
    {
        return $this->belongsTo(ComponentItem::class);
    }

    public function delete(){

        DB::beginTransaction();

        try{
            
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