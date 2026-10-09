<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderReceivedItem;
use App\Models\User;

class PurchaseOrderReceived extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'purchase_order_received';

    protected $fillable = [
        'purchase_order_id',
        'receipt_no',
        'received_date',
        'remarks',
        'created_by',
        'updated_by',
    ];

    public function PurchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function Items(): HasMany
    {
        return $this->hasMany(PurchaseOrderReceivedItem::class, 'purchase_order_received_id');
    }

    public function CreatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function UpdatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
