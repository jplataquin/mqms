<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\DirectPurchaseOrderItem;
use App\Models\PaymentTerm;
use App\Models\Supplier;
use App\Models\Project;
use App\Models\Section;
use App\Models\User;

class DirectPurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'direct_purchase_orders';
    public $deleteException = null;

    protected $fillable = [
        'supplier_id',
        'payment_term_id',
        'project_id',
        'section_id',
        'status',
        'extras',
        'created_by',
        'updated_by',
        'approved_by',
        'rejected_by',
        'void_by',
        'approved_at',
        'rejected_at',
        'void_at',
    ];

    public function Items(): HasMany
    {
        return $this->hasMany(DirectPurchaseOrderItem::class);
    }

    public function User(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function Project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function Section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function PaymentTerm(): HasOne
    {
        return $this->hasOne(PaymentTerm::class, 'id', 'payment_term_id');
    }

    public function Supplier(): HasOne
    {
        return $this->hasOne(Supplier::class, 'id', 'supplier_id');
    }

    public function CreatedByUser()
    {
        $user = User::find($this->created_by);
        return $user ?: User::defaultAttirbutes();
    }

    public function UpdatedByUser()
    {
        $user = User::find($this->updated_by);
        return $user ?: User::defaultAttirbutes();
    }

    public function ApprovedByUser()
    {
        $user = User::find($this->approved_by);
        return $user ?: User::defaultAttirbutes();
    }

    public function RejectedByUser()
    {
        $user = User::find($this->rejected_by);
        return $user ?: User::defaultAttirbutes();
    }

    public function getGrandTotal()
    {
        $items = $this->Items;
        $grand_total = 0;

        foreach ($items as $item) {
            $total = $item->price * $item->quantity;
            $grand_total += $total;
        }

        $extras = json_decode($this->extras) ?? [];

        foreach ($extras as $extra) {
            $grand_total += (float) ($extra->value ?? 0);
        }

        return $grand_total;
    }
}
