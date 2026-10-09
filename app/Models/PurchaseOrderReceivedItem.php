<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\PurchaseOrderReceived;
use App\Models\PurchaseOrderItem;

class PurchaseOrderReceivedItem extends Model
{
    use HasFactory;

    protected $table = 'purchase_order_received_items';

    protected $fillable = [
        'purchase_order_received_id',
        'purchase_order_item_id',
        'quantity_received',
    ];

    public function PurchaseOrderReceived(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderReceived::class, 'purchase_order_received_id');
    }

    public function PurchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
    }
}
