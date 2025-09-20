<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


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
}