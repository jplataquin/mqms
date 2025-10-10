<?php

namespace App\Http\Controllers\Review\Bulk;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Section;
use App\Models\Component;
use App\Models\ComponentItem;
use App\Models\MaterialQuantityRequest;
use App\Models\MaterialQuantityRequestItem;
use App\Models\MaterialQuantity;
use App\Models\MaterialCanvass;
use App\Models\MaterialItem;
use App\Models\Supplier;
use App\Models\PaymentTerm;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Http\Controllers\Controller;


class PurchaseOrderBulkReviewController extends Controller
{

    public function list(){

        //Todo roles 

        $projects = Project::orderBy('name','ASC')->where('status','=','ACTV')->get();

        
        return view('review/purchase_order/bulk/list',[
            'projects' => $projects
        ]);
    }

    public function _list(Request $request){

        $purchase_orders = PurchaseOrder::where('status','PEND')->orWhere('status','REVO')->orderBy('id','ASC');

        $purchase_orders = $purchase_orders->get();
        
        $result         = [];
        $projects       = [];
        $suppliers      = [];
        $payment_terms  = [];

        foreach($purchase_orders as $po){

            if(!isset($result[$po->project_id])){
                $projects[$po->project_id]  = Project::find($po->project_id);
                $result[$po->project_id]    = [];
            }

            if(!isset($suppliers[$po->supplier_id])){
                $suppliers[$po->supplier_id] = Supplier::find($po->supplier_id);
            }

            if(!isset($payment_terms[$po->payment_term_id])){
                $payment_terms[$po->payment_term_id] = PaymentTerm::find($po->payment_term_id);
            }

            $result[$po->project_id][] = $this->review_checklist($po);
        }

        
        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> [
                'projects'          => $projects,
                'payment_terms'     => $payment_terms,
                'suppliers'         => $suppliers,
                'result'            => $result
            ]
        ]);
    }

    private function review_checklist($po){


        $project = $po->Project;

        //Check project if status is active
        if($project->status != 'ACTV'){
            
            return [
                'po'        => $po,
                'flag'      => false,
                'failed'    => ['Project status is not active']
            ];
        }

        $component = $po->Component;

        //Check if component is status approved
        if($component->status != 'APRV'){
           
             return [
                'po'        => $po,
                'flag'      => false,
                'failed'    => ['Component status is not active']
            ];
        }

        $mr                     = $po->MaterialQuantityRequest;
        $mr_items               = $mr->Items;
        $remaining_quantity_arr = [];

        foreach($mr_items as $mr_item){
            
            if( !isset($remaining_quantity_arr[$mr_item->component_item_id]) ){
                $remaining_quantity_arr[$mr_item->component_item_id] = [];
            }

            $total_poed = PurchaseOrderItem::where('component_item_id',$mr_item->component_item_id)
            ->where('material_quantity_request_item_id',$mr_item->id)
            ->where('material_item_id',$mr_item->material_item_id)
            ->where('status','APRV')
            ->sum('quantity');
            
            $remaining_quantity_arr[$mr_item->material_item_id] = $mr_item->requested_quantity - $total_poed;
        }

        $po_items = $po->Items;

        $total = 0;

        
        $flag =true;

        foreach($po_items as $po_item){

            $total =  $total + ($po_item->quantity * $po_item->price); 
        

            if(!isset($remaining_quantity_arr[$po_item->material_item_id])){

                $flag       = false;
                $failed[]   = 'PO Material Item not found in Material Request';
              
            }else if($remaining_quantity_arr[$po_item->material_item_id] < $po_item->quantity && $po->status == 'PEND'){
                
                $flag = false;

                $failed[] = 'Approved Material Request quantity ('.number_format($remaining_quantity_arr[$po_item->material_item_id],2).') is less than the PO item quantity ('.number_format($po_item->quantity,2).')';
            }


        }

        $extras = json_decode($po->extras);

        foreach($extras as $extra){
           $total = $total + (float) $extra['value'];
        }

        return [
            'po'            => $po,
            'total'         => $total,
            'flag'          => $flag,
            'failed'        => $failed    
        ];


    }

}
