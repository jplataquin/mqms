<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\MaterialQuantityRequestItem;
use App\Models\MaterialQuantity;
use App\Models\PurchaseOrder;
use App\Models\Project;
use App\Models\Section;
use App\Models\ContractItem;
use App\Models\Component;
use App\Models\ComponentItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class MaterialQuantityRequest extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'material_quantity_requests';
    
   
    public $deleteException = null;

    public function getDateNeededAttribute( $value ) {

        if(!$value) return '';
        
        return (new Carbon($value))->format('M d, Y');
    }

    // protected function casts(): array
    // {

    //     return [

    //         'date_needed' => 'datetime:M d, Y',

    //     ];

    // }

    private function get_total_approved_component_item_quantity($component_item_id){
        
        $component_item = ComponentItem::find($component_item_id);

        if(!$component_item) return 0;

        $component = $component_item->Component;

        if($component->status != 'APRV') return 0;

       return  $component_item->quantity;
    }

    private function get_total_approved_request_item_quantity(
        $material_quantity_request_item_id,
        $component_item_id,
        $material_item_id
    ){

        
        $total_approved_quantity = 0;


        $material_quantity = MaterialQuantity::where('component_item_id',$component_item_id)
        ->where('material_item_id',$material_item_id)
        ->first();

        
        $material_quantity_request_item = MaterialQuantityRequestItem::where(function($query){
            $query->where('status','=','APRV')->orWhere('status','=','CLSD');
        })
        ->where('component_item_id','=',$component_item_id)
        ->where('material_item_id','=',$material_item_id);
        
    
        if($material_quantity_request_item_id){
            
            $total_approved_quantity = $material_quantity_request_item
            ->where('id','!=',$material_quantity_request_item_id)
            ->sum('requested_quantity');

        }else{
            
            $total_approved_quantity = $material_quantity_request_item
            ->sum('requested_quantity');
            
        }
        
        
        return $total_approved_quantity * $material_quantity->equivalent;
    }

    public function getHashCode(){

        $item_str = '';
        
        foreach($this->Items as $item){

            $total_approved_component_item_quantity = get_total_approved_component_item_quantity($item->component_item_id);
            
            $total_approved_request_item_quantity = $this->get_total_approved_request_item_quantity(
                $item->id,
                $item->component_item_id,
                $item->material_item_id
            );

            $item_str = $item_str.':'.$total_approved_component_item_quantity.'-'.$total_approved_request_item_quantity.'-'.$item->material_item_id.'-'.$item->requested_quantity;
        }

        $secret_text = $item_str.'secret';

        $hash = Hash::make($secret_text);

        return [
            'hash' => $hash,
            'secret_text' => $secret_text
        ];
    }

    public function Items(): HasMany
    {
        return $this->hasMany(MaterialQuantityRequestItem::class);
    }

    public function PurchaseOrder(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
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

    public function ContractItem(): BelongsTo
    {
        return $this->belongsTo(ContractItem::class);
    }

    public function User(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function CreatedByUser(){   

        $user = User::find($this->created_by);

        if(!$user){
            return User::defaultAttirbutes();
        }

        return $user;
    }

    public function UpdatedByUser(){   
       
        $user = User::find($this->updated_by);

        if(!$user){
            return User::defaultAttirbutes();
        }

        return $user;
    }

    public function ApprovedByUser(){   
       
        $user = User::find($this->approved_by);

        if(!$user){
            return User::defaultAttirbutes();
        }

        return $user;
    }

    public function RejectedByUser(){   
       
        $user = User::find($this->rejected_by);

        if(!$user){
            return User::defaultAttirbutes();
        }

        return $user;
    }

}
