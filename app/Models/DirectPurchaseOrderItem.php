<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\DirectPurchaseOrder;
use App\Models\DirectPurchaseOrderReceivedItem;
use App\Models\MaterialItem;

class DirectPurchaseOrderItem extends Model
{
    use HasFactory;

    protected $table = 'direct_purchase_order_items';

    protected $fillable = [
        'direct_purchase_order_id',
        'direct_material_quantity_request_item_id',
        'direct_material_canvass_id',
        'material_item_id',
        'status',
        'quantity',
        'order_quantity',
        'price',
        'remarks',
    ];

    public function ReceivedItems(): HasMany
    {
        return $this->hasMany(DirectPurchaseOrderReceivedItem::class, 'direct_purchase_order_item_id');
    }

    public function getReceivedQuantityAttribute()
    {
        return (float) $this->ReceivedItems()->sum('quantity_received');
    }

    public function getRemainingQuantityAttribute()
    {
        return max(0, (float) $this->quantity - $this->received_quantity);
    }

    public function DirectPurchaseOrder(): BelongsTo
    {
        return $this->belongsTo(DirectPurchaseOrder::class, 'direct_purchase_order_id');
    }

    public function MaterialItem(): BelongsTo
    {
        return $this->belongsTo(MaterialItem::class, 'material_item_id');
    }

    public function DirectMaterialQuantityRequestItem(): BelongsTo
    {
        return $this->belongsTo(DirectMaterialQuantityRequestItem::class, 'direct_material_quantity_request_item_id');
    }

    public function DirectMaterialCanvass(): BelongsTo
    {
        return $this->belongsTo(DirectMaterialCanvass::class, 'direct_material_canvass_id');
    }
}
