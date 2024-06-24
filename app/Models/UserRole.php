<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class UserRole extends Model
{
    use HasFactory;

    protected $table = 'user_roles';

    public function User(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
