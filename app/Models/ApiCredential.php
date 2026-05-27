<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class ApiCredential extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'api_credentials';

    protected $fillable = [
        'name',
        'api_key',
        'secret_key',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function CreatedByUser()
    {
        $user = User::find($this->created_by);

        if (!$user) {
            return User::defaultAttirbutes();
        }

        return $user;
    }

    public function UpdatedByUser()
    {
        $user = User::find($this->updated_by);

        if (!$user) {
            return User::defaultAttirbutes();
        }

        return $user;
    }

    public function DeletedByUser()
    {
        $user = User::find($this->deleted_by);

        if (!$user) {
            return User::defaultAttirbutes();
        }

        return $user;
    }
}
