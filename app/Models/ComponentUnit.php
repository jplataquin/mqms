<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class ComponentUnit extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'component_units';

    //To do make this into a callback later on
    public static function toOptions(Array $ids = []){

        if(count($ids)){    
            $rows = parent::whereIn('id',$ids)->where('deleted_at','=',null)->get();
        }else{
            $rows = parent::where('deleted_at','=',null)->get();
        }
        

        $result = [];
        
        foreach($rows as $row){
            $result[$row->id] = (object) [
                'id'    => $row->id,
                'text'  => $row->text
            ];
        }

        return $result;
    }
}
