<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Component;
use App\Models\User;

class Accomplishment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'accomplishments';

    protected $fillable = [
        'component_id',
        'type',
        'entry_date',
        'quantity',
        'remarks',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
        'entry_date' => 'date',
        'quantity' => 'float',
    ];

    public function Component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    public function CreatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function UpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function DeletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
