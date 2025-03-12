<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\User;
use App\Models\Role;

class UserRole extends Model
{
    use HasFactory;

    protected $table = 'user_roles';

    public function User(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function Data(): HasOne
    {
        return $this->belongsTo(Role::class);
    }
}
