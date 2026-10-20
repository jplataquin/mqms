<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\DirectPurchaseOrder;
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
