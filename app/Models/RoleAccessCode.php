<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    public function Data(): HasOne
    {
        return $this->hasOne(AccessCode::class,'id','access_code_id');
    }
}
