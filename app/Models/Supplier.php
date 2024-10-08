<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Supplier extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'suppliers';
  
    public static function toOptions(Array $ids = []){

        if(count($ids)){    
            $rows = parent::whereIn('id',$ids)->orderBy('text','ASC')->where('deleted_at',null)->get();
        }else{
            $rows = parent::orderBy('text','ASC')->where('deleted_at',null)->get();
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
