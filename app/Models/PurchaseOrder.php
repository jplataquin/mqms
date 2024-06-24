<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\PurchseOrderItem;
use App\Models\MaterialQuantityRequest;
use App\Models\PaymentTerm;
use App\Models\Supplier;
use App\Models\Project;
use App\Models\Section;
use App\Models\Component;
use App\Models\User;

class PurchaseOrder extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'purchase_orders';
    public $deleteException = null;

    public function Items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function User(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function MaterialQuantityReqest(): BelongsTo
    {
        return $this->belongsTo(MaterialQuantityRequest::class);
    }

    public function Project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function Section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function Component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    public function PaymentTerm(): HasOne
    {
        return $this->hasOne(PaymentTerm::class,'id','payment_term_id');
    }

    public function Supplier(): HasOne
    {
        return $this->hasOne(Supplier::class,'id','supplier_id');
    }
    
}