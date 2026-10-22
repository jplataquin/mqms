<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\DirectPurchaseOrder;
use App\Models\DirectPurchaseOrderReceivedItem;
use App\Models\User;

class DirectPurchaseOrderReceived extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'direct_purchase_order_received';

    protected $fillable = [
        'direct_purchase_order_id',
        'receipt_no',
        'received_date',
        'remarks',
        'created_by',
        'updated_by',
    ];

    public function DirectPurchaseOrder(): BelongsTo
    {
        return $this->belongsTo(DirectPurchaseOrder::class, 'direct_purchase_order_id');
    }

    public function Items(): HasMany
    {
        return $this->hasMany(DirectPurchaseOrderReceivedItem::class, 'direct_purchase_order_received_id');
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
