<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Role;
use App\Models\AccessCode;

class RoleAccessCode extends Model
{
    use HasFactory;

    protected $table = 'role_access_codes';

    public function Role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function AccessCodes(): HasMany
    {
        return $this->hasMany(AccessCode::class);
    }
}
