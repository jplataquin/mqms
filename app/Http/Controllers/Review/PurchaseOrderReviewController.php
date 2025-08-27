<?php

namespace App\Http\Controllers\Review;

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


class PurchaseOrderReviewController extends Controller
{

    public function list(){

        $projects = Project::orderBy('name','ASC')->where('status','=','ACTV')->get();

        
        return view('review/purchase_order/list',[
            'projects' => $projects
        ]);
    }

    public function _list(Request $request){

   
        $page            = (int) $request->input('page')     ?? 1;
        $limit           = (int) $request->input('limit')    ?? 10;
        $project_id      = (int) $request->input('project_id')  ?? 0;
        $section_id      = (int) $request->input('section_id')  ?? 0;
        $component_id    = (int) $request->input('component_id')  ?? 0;
        $query           = (int) $request->input('query')    ?? 0;
        $orderBy         = $request->input('order_by')       ?? 'id';
        $order           = $request->input('order')          ?? 'DESC';
        $result = [];

        $purchaseOrder = new PurchaseOrder();

        $purchaseOrder = $purchaseOrder->where(function($query){
            $query->where('status','=','PEND')->orWhere('status','=','REVO');
        });
        
        
        if($query){
            $purchaseOrder = $purchaseOrder->where('id','=',$query);
        }

        if($project_id){
            
            $purchaseOrder = $purchaseOrder->where('project_id','=',$project_id);

            if($section_id){
                $purchaseOrder = $purchaseOrder->where('section_id','=',$section_id);

                if($component_id){
                    $purchaseOrder = $purchaseOrder->where('component_id','=',$component_id);

                }
            }
        }

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $purchaseOrder->orderBy($orderBy,$order)->skip($page)->take($limit)->with('Project')->with('Section')->with('Component')->with('User')->get();
            
        }else{

            $result = $purchaseOrder->orderBy($orderBy,$order)->take($limit)->with('Project')->with('Section')->with('Component')->with('User')->get();
        }

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> $result
        ]);
    }

    public function display($id){
        
        $purchaseOrder           = PurchaseOrder::findOrFail($id);
        $materialQuantityRequest = MaterialQuantityRequest::findOrFail($purchaseOrder->material_quantity_request_id);

        if(!$this->hasAccess('purchase_order:all:view')){
            return view('access_denied');
        }

        $project                = $materialQuantityRequest->Project;
        $section                = $materialQuantityRequest->Section;
        $contract_item          = $materialQuantityRequest->ContractItem;
        $component              = $materialQuantityRequest->Component;
        $material_reqeust_items = $materialQuantityRequest->Items;
        //TODO compensate for delted items
        $componentItems         = $component->ComponentItems;
        $paymentTerm            = $purchaseOrder->PaymentTerm;
        $supplier               = $purchaseOrder->Supplier;
        $items                  = $purchaseOrder->Items;
        
        $material_item_id_arr           = [];
        $componentItemMaterialsArr      = [];
        $componentItemArr               = [];

        //Arrange remaining quantity value for easy access
        $remaining_quantity_arr = [];

        foreach($material_reqeust_items as $mr_item){
            
            if( !isset($remaining_quantity_arr[$mr_item->component_item_id]) ){
                $remaining_quantity_arr[$mr_item->component_item_id] = [];
            }

            $total_poed = PurchaseOrderItem::where('component_item_id',$mr_item->component_item_id)
            ->where('material_quantity_request_item_id',$mr_item->id)
            ->where('material_item_id',$mr_item->material_item_id)
            ->where('status','APRV')
            ->sum('quantity');
            
            //echo $mr_item->id.' '.$mr_item->component_item_id.' '.$mr_item->material_item_id.' '.$mr_item->requested_quantity.'  '.$total_poed.'<br>';
            $remaining_quantity_arr[$mr_item->component_item_id][$mr_item->material_item_id] = $mr_item->requested_quantity - $total_poed;
        }

        //Arrange items into component item
        foreach($items as $item){

            $material_item_id_arr[] = $item->material_item_id;

            
            if(!isset($componentItemMaterialsArr[$item->component_item_id])){
                $componentItemMaterialsArr[$item->component_item_id] = [];
            }

            $componentItemMaterialsArr[$item->component_item_id][] = $item;
        }
        
        
        //Arrange component items
        foreach($componentItems as $componentItem){
            $componentItemArr[$componentItem->id] = $componentItem;
        }

        $materialItems      = MaterialItem::whereIn('id',$material_item_id_arr)->get();
        $materialItemArr    = [];

        //Arrange material items
        foreach($materialItems as $materialItem){
            $materialItemArr[$materialItem->id] = $materialItem;
        }

        $extras = json_decode($purchaseOrder->extras);

        $po_details = [
            "PO Number"             => str_pad($purchaseOrder->id,6,0,STR_PAD_LEFT),
            "Project"               => $project->name,
            "Section"               => $section->name,
            "Contract Item"         => $contract_item->name,
            "Component"             => $component->name,
            "Material Request ID"   => [
                'text' => str_pad($materialQuantityRequest->id,6,0,STR_PAD_LEFT),
                'href' => "/material_quantity_request/".$materialQuantityRequest->id
            ],
            "Status"                => $purchaseOrder->status,
            "Description"           => $materialQuantityRequest->description,
            "Created By"            => $purchaseOrder->CreatedByUser()->name.' '.$purchaseOrder->created_at            
        ];


        if($purchaseOrder->updated_at && $purchaseOrder->UpdatedByUser()->name){
            $po_details["Updated By"] = $purchaseOrder->UpdatedByUser()->name.' '.$purchaseOrder->updated_at;
        }

        if($purchaseOrder->approved_at && $purchaseOrder->ApprovedByUser()->name){
            $po_details["Approved By"] = $purchaseOrder->ApprovedByUser()->name.' '.$purchaseOrder->approved_at;
        }

        if($purchaseOrder->rejected_at && $purchaseOrder->RejectedByUser()->name){
            $po_details["Rejected By"] = $purchaseOrder->RejectedByUser()->name.' '.$purchaseOrder->rejected_at;
        }

        return view('review/purchase_order/display',[
            'purchase_order'            => $purchaseOrder,
            'material_quantity_request' => $materialQuantityRequest,
            'remaining_quantity_arr'    => $remaining_quantity_arr,
            'project'                   => $project,
            'section'                   => $section,
            'contract_item'             => $contract_item,
            'component'                 => $component,
            'supplier'                  => $supplier,
            'payment_term'              => $paymentTerm,
            'items'                     => $items,
            'extras'                    => $extras,
            'materialItemArr'           => $materialItemArr,
            'componentItemArr'          => $componentItemArr,
            'componentItemMaterialsArr' => $componentItemMaterialsArr,
            'po_details'                => $po_details
        ]);
    }

    public function _approve(Request $request){

        if(!$this->hasAccess('purchase_order:all:approve')){
            return response()->json([
                'status'    => 0,
                'message'   => 'Access Denied',
                'data'      => []
            ]);
        }

        $id = (int) $request->input('id');

        $purchaseOrder = PurchaseOrder::find($id);

        if(!$purchaseOrder){

            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }
        
        if($purchaseOrder->status != 'PEND'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record cannot be approved, status is not pending',
                'data'      => []
            ]);
        }

        $user_id = Auth::user()->id;

        

        DB::beginTransaction();

        try {  

            $purchaseOrder->approved_by = $user_id;
            $purchaseOrder->status      = 'APRV';
            $purchaseOrder->approved_at = Carbon::now();
            
            $purchaseOrder->save();
            

            DB::table('purchase_order_items')->where('purchase_order_id',$purchaseOrder->id)
            ->update([
                'status' => 'APRV'
            ]);

            DB::commit();
            
            return response()->json([
                'status' => 1,
                'message' => '',
                'data' => [
                    'id' => $purchaseOrder->id
                ]
            ]);
            
            
        }catch(\Exception $e){

            return response()->json([
                'status'    => 0,
                'message'   => $e->getMessage(),
                'data'      => []
            ]);

            DB::rollback();

            return false;
        
        }
        
    }

    public function _reject(Request $request){

         if(!$this->hasAccess('purchase_order:all:reject')){
            return response()->json([
                'status'    => 0,
                'message'   => 'Access Denied',
                'data'      => []
            ]);
        }

        $id = (int) $request->input('id');

        $purchaseOrder = PurchaseOrder::find($id);

        if(!$purchaseOrder){

            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }

        if($purchaseOrder->status != 'PEND'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record cannot be disapproved, status is not pending',
                'data'      => []
            ]);
        }

        $user_id = Auth::user()->id;

        DB::beginTransaction();

        try {  

            $purchaseOrder->approved_by = $user_id;
            $purchaseOrder->status      = 'REJC';
            $purchaseOrder->approved_at = Carbon::now();
            
            $purchaseOrder->save();
            

            DB::table('purchase_order_items')->where('purchase_order_id',$purchaseOrder->id)
            ->update([
                'status' => 'REJC'
            ]);

            DB::commit();
            
            return response()->json([
                'status' => 1,
                'message' => '',
                'data' => [
                    'id' => $purchaseOrder->id
                ]
            ]);
            

        }catch(\Exception $e){

            return response()->json([
                'status'    => 0,
                'message'   => $e->getMessage(),
                'data'      => []
            ]);

            DB::rollback();

            return false;
        
        }

        
    }

    
    public function _void(Request $request){

        if(!$this->hasAccess('purchase_order:all:void')){
            return response()->json([
                'status'    => 0,
                'message'   => 'Access Denied',
                'data'      => []
            ]);
        }

        $id = (int) $request->input('id');

        $purchaseOrder = PurchaseOrder::find($id);

        if(!$purchaseOrder){

            return response()->json([
                'status'    => 0,
                'message'   => 'Error: Record not found',
                'data'      => []
            ]);
        }

        if($purchaseOrder->status != 'REVO'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Error: Record cannot be void, record status is wrong',
                'data'      => []
            ]);
        }

        $user_id = Auth::user()->id;

        DB::beginTransaction();

        try {  

            $purchaseOrder->approved_by = $user_id;
            $purchaseOrder->status      = 'VOID';
            $purchaseOrder->approved_at = Carbon::now();
            
            $purchaseOrder->save();
            

            DB::table('purchase_order_items')->where('purchase_order_id',$purchaseOrder->id)
            ->update([
                'status' => 'VOID'
            ]);

            DB::commit();
            
            return response()->json([
                'status' => 1,
                'message' => '',
                'data' => [
                    'id' => $purchaseOrder->id
                ]
            ]);

        }catch(\Exception $e){

            return response()->json([
                'status'    => 0,
                'message'   => $e->getMessage(),
                'data'      => []
            ]);

            DB::rollback();

            return false;
        
        }

    }


    public function _reject_void(Request $request){

        if(!$this->hasAccess('purchase_order:all:reject_void_request')){
            return response()->json([
                'status'    => 0,
                'message'   => 'Access Denied',
                'data'      => []
            ]);
        }

        $id = (int) $request->input('id');

        $purchaseOrder = PurchaseOrder::find($id);

        if(!$purchaseOrder){

            return response()->json([
                'status'    => 0,
                'message'   => 'Error: Record not found',
                'data'      => []
            ]);
        }

        if($purchaseOrder->status != 'REVO'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Error: Cannot reject void, record has wrong status',
                'data'      => []
            ]);
        }

        $user_id = Auth::user()->id;

        DB::beginTransaction();

        try {  

            $purchaseOrder->approved_by = $user_id;
            $purchaseOrder->status      = 'APRV';
            $purchaseOrder->approved_at = Carbon::now();
            
            $purchaseOrder->save();
            

            DB::table('purchase_order_items')->where('purchase_order_id',$purchaseOrder->id)
            ->update([
                'status' => 'APRV'
            ]);

            DB::commit();
            
            return response()->json([
                'status' => 1,
                'message' => '',
                'data' => [
                    'id' => $purchaseOrder->id
                ]
            ]);

        }catch(\Exception $e){

            return response()->json([
                'status'    => 0,
                'message'   => $e->getMessage(),
                'data'      => []
            ]);

            DB::rollback();

            return false;
        
        }

    }
  
}
