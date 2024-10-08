<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\MaterialGroup;
use Illuminate\Support\Facades\Auth;

class MaterialItem extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'material_items';

    public function Group(): HasOne
    {
        return $this->hasOne(MaterialGroup::class);
    }

    //To do make this into a callback later on
    public static function toOptions(Array $ids = []){

        if(count($ids)){    
            $rows = parent::whereIn('id',$ids)->get();
        }else{
            $rows = parent::get();
        }
        

        $result = [];
        
        foreach($rows as $row){
            $result[] = (object) [
                'id'    => $row->id,
                'text'  => trim($row->brand.' '.$row->name.' '.$row->specification_unit_packaging)
            ];
        }

        return $result;
    }
}
