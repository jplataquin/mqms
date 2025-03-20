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

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $table = 'purchase_order_items';
    public $deleteException = null;

    public function MaterialQuantityRequestItem(): HasOne
    {
        return $this->hasOne(MaterialQuantityRequestItem::class);
    }

    public function PurchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function MaterialCanvass(): HasOne
    {
        return $this->belongsTo(MaterialCanvass::class);
    }

    public function MaterialItem(): HasOne
    {
        return $this->belongsTo(MaterialItem::class);
    }
    
}