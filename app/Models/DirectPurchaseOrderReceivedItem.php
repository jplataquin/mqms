<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\DirectPurchaseOrderReceived;
use App\Models\DirectPurchaseOrderItem;

class DirectPurchaseOrderReceivedItem extends Model
{
    use HasFactory;

    protected $table = 'direct_purchase_order_received_items';

    protected $fillable = [
        'direct_purchase_order_received_id',
        'direct_purchase_order_item_id',
        'quantity_received',
    ];

    public function DirectPurchaseOrderReceived(): BelongsTo
    {
        return $this->belongsTo(DirectPurchaseOrderReceived::class, 'direct_purchase_order_received_id');
    }

    public function DirectPurchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(DirectPurchaseOrderItem::class, 'direct_purchase_order_item_id');
    }
}
