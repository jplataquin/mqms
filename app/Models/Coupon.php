<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class Coupon extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'coupons';

    
    public function generateCode($salt, $amount){
        
        $secret_text = $salt.'='.$amount.'='.env('APP_KEY','');

        $hash = hash('sha256',$secret_text);

        return $hash;

        //$hash = substr($raw_hash,0,3).substr($raw_hash,61,3);
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

    public function ApprovedByUser(){   
       
        $user = User::find($this->approved_by);

        if(!$user){
            return User::defaultAttirbutes();
        }

        return $user;
    }

    public function RejectedByUser(){   
       
        $user = User::find($this->rejected_by);

        if(!$user){
            return User::defaultAttirbutes();
        }

        return $user;
    }

    public function ProcessedByUser(){   

        $user = User::find($this->processed_by);

        if(!$user){
            return User::defaultAttirbutes();
        }

        return $user;
    }

    public function VoidByUser(){   

        $user = User::find($this->void_by);

        if(!$user){
            return User::defaultAttirbutes();
        }

        return $user;
    }
}