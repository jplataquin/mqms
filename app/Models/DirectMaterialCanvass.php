<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DirectMaterialCanvass extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'direct_material_canvass';

    public function Request(): BelongsTo
    {
        return $this->belongsTo(DirectMaterialQuantityRequest::class, 'direct_material_quantity_request_id');
    }

    public function Item(): BelongsTo
    {
        return $this->belongsTo(DirectMaterialQuantityRequestItem::class, 'direct_material_quantity_request_item_id');
    }

    public function Supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function PaymentTerm(): BelongsTo
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_term_id');
    }

    public function Creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function Approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function Rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function Voider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'void_by');
    }
}
