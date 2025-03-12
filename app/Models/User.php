<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\UserRole;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function Roles(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    public function statusOptions(){
        return [
            'ACTV' => 'Active',
            'DCTV' => 'Deactivated'
        ];
    }

    public static function defaultAttirbutes(){
        return (object) [
            'id' => '',
            'name' => '',
            'email' => ''
        ];
    }

    public function getAccessCodes(){

        $access_codes = [];

        $roles = $this->Roles;

        foreach($roles as $role){
            $access_codes = $role->access_codes;

            foreach($access_codes as $code){

                if(!in_array($code,$access_codes)){
                    $access_codes[] = $code;
                }
            }
        }

        return $access_codes;
    }
}
