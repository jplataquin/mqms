<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class Unit extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'units';

    //To do make this into a callback later on
    public static function toOptions(Array $ids = []){

        if(count($ids)){    
            $rows = parent::whereIn('id',$ids)->orderBy('text','ASC')->get();
        }else{
            $rows = parent::orderBy('text','ASC')->get();
        }
        

        $result = [];
        
        foreach($rows as $row){
            $result[$row->id] = (object) [
                'id'    => $row->id,
                'text'  => $row->text,
                'deleted' => (boolean) ($row->deleted_at != null)
            ];
        }

       
       
        return $result;
    }
}
