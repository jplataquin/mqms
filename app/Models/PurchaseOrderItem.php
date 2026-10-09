<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\PurchseOrder;
use App\Models\MaterialQuantityRequestItem;
use App\Models\MaterialCanvass;
use App\Models\PurchaseOrderReceivedItem;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $table = 'purchase_order_items';
    public $timestamps = false;
    public $deleteException = null;

    public function ReceivedItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderReceivedItem::class, 'purchase_order_item_id');
    }

    public function getReceivedQuantityAttribute()
    {
        return (float) $this->ReceivedItems()->sum('quantity_received');
    }

    public function getRemainingQuantityAttribute()
    {
        return max(0, (float) $this->quantity - $this->received_quantity);
    }

    public function MaterialQuantityRequestItem(): HasOne
    {
        return $this->hasOne(MaterialQuantityRequestItem::class);
    }

    public function PurchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function MaterialCanvass(): BelongsTo
    {
        return $this->belongsTo(MaterialCanvass::class);
    }

    public function MaterialItem(): BelongsTo
    {
        return $this->belongsTo(MaterialItem::class);
    }
    
}