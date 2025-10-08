<?php

namespace App\Http\Controllers;

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
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;
use Illuminate\Support\Str;


class PurchaseOrderController extends Controller
{
    public function list(){

        $projects = Project::orderBy('name','ASC')->where('status','=','ACTV')->get();

        $material_items = MaterialItem::orderBy('name','ASC')->get();

        return view('purchase_order/list',[
            'projects'          => $projects,
            'material_items'    => $material_items
        ]);
    }

 
    public function _list(Request $request){


        $user = auth()->user();

        $page              = (int) $request->input('page')     ?? 1;
        $limit             = (int) $request->input('limit')    ?? 10;
        $project_id        = (int) $request->input('project_id')  ?? 0;
        $section_id        = (int) $request->input('section_id')  ?? 0;
        $contract_item_id  = (int) $request->input('contract_item_id') ?? 0;
        $component_id      = (int) $request->input('component_id')  ?? 0;
        $query             = (int) $request->input('query')    ?? 0;
        $material_item_id  = (int) $request->input('material_item_id') ?? 0;
        $status            = $request->input('status')         ?? '';
        $orderBy           = $request->input('order_by')       ?? 'id';
        $order             = $request->input('order')          ?? 'DESC';
        
        $result = [];
    
        $purchaseOrder = new PurchaseOrder();
        
         
        if($query){
            $purchaseOrder = $purchaseOrder->where('id','=',$query);
        }


        //Check roles
        if($this->hasAccess(['purchase_order:own:list']) && !$this->hasAccess(['purchase_order:all:list'])){

            $purchaseOrder = $purchaseOrder->where('created_by','=',$user->id);
        
        }else if(!$this->hasAccess(['purchase_order:own:list']) && !$this->hasAccess(['purchase_order:all:list'])){

            return response()->json([
                'status'    => 0,
                'message'   =>'Access Denied',
                'data'      => []
            ]);
        }
 
         if($project_id){
             
             $purchaseOrder = $purchaseOrder->where('project_id','=',$project_id);
 
             if($section_id){
                 $purchaseOrder = $purchaseOrder->where('section_id','=',$section_id);
                
                 if($contract_item_id){

                    $purchaseOrder = $purchaseOrder->where('contract_item_id','=',$contract_item_id);
                    
                    if($component_id){
                        $purchaseOrder = $purchaseOrder->where('component_id','=',$component_id);
    
                    }
                 }
             }
         }

         
         if($status){
            $purchaseOrder = $purchaseOrder->where('status','=',$status);           
         }


         if($material_item_id){

            
            $po_id_filter = [];
            
            $purchase_order_items = PurchaseOrderItem::select('purchase_order_id')->where('material_item_id',$material_item_id)->groupBy('purchase_order_id')->get();

            foreach($purchase_order_items as $po_item){
                $po_id_filter[] = $po_item->purchase_order_id;
            }

            if($po_id_filter){
                $purchaseOrder = $purchaseOrder->whereIn('id',$po_id_filter);
            }
         }

         if($limit > 0){
             $page   = ($page-1) * $limit;
             
             $result = $purchaseOrder->orderBy($orderBy,$order)->skip($page)->take($limit)->with('Project')->get();
             
         }else{
 
             $result = $purchaseOrder->orderBy($orderBy,$order)->take($limit)->with('Project')->get();
         }
 
         return response()->json([
             'status' => 1,
             'message'=>'',
             'data'=> $result
         ]);
    }

    public function select(){

        if( !$this->hasAccess(['purchase_order:own:create']) ){
            return view('access_denied');
        }

        $projects = Project::orderBy('name','ASC')->where('status','=','ACTV')->get();

        return view('purchase_order/select',[
            'projects'          => $projects
        ]);
    }

    public function _select_material_request(Request $request){

        //todo check role
        if( !$this->hasAccess(['purchase_order:own:create']) ){
            return view('access_denied');
        }

        $page               = (int) $request->input('page')             ?? 1;
        $limit              = (int) $request->input('limit')            ?? 10;
        $project_id         = (int) $request->input('project_id')       ?? 0;
        $section_id         = (int) $request->input('section_id')       ?? 0;
        $contract_item_id   = (int) $request->input('contract_item_id') ?? 0;
        $component_id       = (int) $request->input('component_id')     ?? 0;
        $query              = (int) $request->input('query')            ?? 0;
        $orderBy            = $request->input('order_by')               ?? 'id';
        $order              = $request->input('order')                  ?? 'DESC';
        $result = [];

        $materialQuantityRequest = new MaterialQuantityRequest();

        $materialQuantityRequest = $materialQuantityRequest->where('status','=','APRV');
        
        
        $materialQuantityRequest = $materialQuantityRequest->whereIn('id',  function($query)
        {
            $query->select('material_canvass.material_quantity_request_id')
                  ->from('material_canvass')
                  ->where('material_canvass.status', '=', 'APRV')
                  ->where('material_canvass.deleted_at','=',null);
        });

        if($query){
            $materialQuantityRequest = $materialQuantityRequest->where('id','=',$query);
        }

        if($project_id){   
            $materialQuantityRequest = $materialQuantityRequest->where('project_id','=',$project_id);
        }

        if($section_id){
                
            $materialQuantityRequest = $materialQuantityRequest->where('section_id','=',$section_id);
           
        }

        if($contract_item_id){     
            $materialQuantityRequest = $materialQuantityRequest->where('contract_item_id','=',$contract_item_id);
        }

        if($component_id){
            $materialQuantityRequest = $materialQuantityRequest->where('component_id','=',$component_id);
        }

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $materialQuantityRequest->orderBy($orderBy,$order)->skip($page)->take($limit)
            ->with('Project')
            ->with('Section')
            ->with('ContractItem')
            ->with('Component')
            ->with('User')
            ->get();
            
        }else{

            $result = $materialQuantityRequest->orderBy($orderBy,$order)->take($limit)
            ->with('Project')
            ->with('Section')
            ->with('ContractItem')
            ->with('Component')
            ->with('User')->get();
        }

        $results_to_show = [];

        //Filter out completed material request
        foreach($result as $mr){
            
            $items                  = $mr->Items;
            $available_item_count   = count($items);
            
            foreach($items as $item){

                //Get sum of all po item quantities for the request item
                $po_quantity = PurchaseOrderItem::where('material_quantity_request_item_id',$item->id)
                ->where('status','!=','VOID')->sum('quantity');

                //Check if all requested quanty has been po'ed.
                if($po_quantity >= $item->requested_quantity){
                    $available_item_count = $available_item_count - 1;
                }
            }

            //Include only request with remaining po'able items
            if($available_item_count > 0){
                $results_to_show[] = $mr;
            }
        }   

        return response()->json([
            'status'    => 1,
            'message'   =>'',
            'data'      => $results_to_show
        ]);
    }

    public function display($id){
        
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $user          = auth()->user();

        if(!$this->hasAccess(['purchase_order:all:view'])){

            if( !$this->hasAccess(['purchase_order:own:view']) ){
                return view('access_denied');
            }

            if($purchaseOrder->created_by != $user->id){
               return view('access_denied');
            }
        }
        
        $materialQuantityRequest = MaterialQuantityRequest::findOrFail($purchaseOrder->material_quantity_request_id);

        $project                = $materialQuantityRequest->Project;
        $section                = $materialQuantityRequest->Section;
        $contract_item          = $materialQuantityRequest->ContractItem;
        $component              = $materialQuantityRequest->Component;
        
        $componentItems                 = $component->ComponentItems;
        $paymentTerm                    = $purchaseOrder->PaymentTerm;
        $supplier                       = $purchaseOrder->Supplier;
        $materialQuantityRequestItems   = $purchaseOrder->Items;
        
        $material_id_arr                            = [];
        $componentItemMaterialsArr                  = [];
        $componentItemArr                           = [];

        //Arrange material quantity request items by id
        foreach($materialQuantityRequestItems as $item){

            $material_id_arr[] = $item->material_item_id;

            
            if(!isset($componentItemMaterialsArr[$item->component_item_id])){
                $componentItemMaterialsArr[$item->component_item_id] = [];
            }

            $componentItemMaterialsArr[$item->component_item_id][] = $item;
        }

        //Arrange component item by id
        foreach($componentItems as $componentItem){
            $componentItemArr[$componentItem->id] = $componentItem;
        }

        $materialItems      = MaterialItem::whereIn('id',$material_id_arr)->get();
        $materialItemArr    = [];

        //Arrange material items by id
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

        $check_quantity = $this->check_for_over_quantity($purchaseOrder);

        return view('purchase_order/display',[
            'check_quantity'                => $check_quantity,
            'purchase_order'                => $purchaseOrder,
            'supplier'                      => $supplier,
            'payment_term'                  => $paymentTerm,
            'extras'                        => $extras,
            'materialItemArr'               => $materialItemArr,
            'componentItemArr'              => $componentItemArr,
            'componentItemMaterialsArr'     => $componentItemMaterialsArr,
            'po_details'                    => $po_details
        ]);
    }


    private function check_for_over_quantity($po){


        // $project = $po->Project;

        // //Check project if status is active
        // if($project->status != 'ACTV'){
            
        //     return [
        //         'po'        => $po,
        //         'flag'      => false,
        //         'failed'    => ['Project status is not active']
        //     ];
        // }

        // $component = $po->Component;

        // //Check if component is status approved
        // if($component->status != 'APRV'){
           
        //      return [
        //         'po'        => $po,
        //         'flag'      => false,
        //         'failed'    => ['Component status is not active']
        //     ];
        // }

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

        $po_item_arr = [];

        foreach($po_items as $po_item){

            if(!isset($po_item_arr[$po_item->id])){
                $po_item_arr[$po_item->id] = [];
            }

            if(!isset($remaining_quantity_arr[$po_item->material_item_id])){

                $po_item_arr[$po_item->id][] = 'PO Material Item not found in Material Request';
                continue;
            }

            if($remaining_quantity_arr[$po_item->material_item_id] < $po_item->quantity && $po->status == 'PEND'){
         

                $po_item_arr[$po_item->id][] = 'Approved Material Request quantity is less than the PO item quantity '.$remaining_quantity_arr[$po_item->material_item_id].' < '.$po_item->quantity;
            }
        }

       
        return $po_item_arr;

    }


    public function create($id){
        
        if(!$this->hasAccess(['purchase_order:own:create'])){
            return view('access_denied');
        }

        $materialQuantityRequest = MaterialQuantityRequest::findOrFail($id);

        if($materialQuantityRequest->status != 'APRV'){
            return show404();
        }

        $project                        = $materialQuantityRequest->Project;
        $section                        = $materialQuantityRequest->Section;
        $contract_item                  = $materialQuantityRequest->ContractItem;
        $component                      = $materialQuantityRequest->Component;
        $materialQuantityRequestItems   = $materialQuantityRequest->Items()->with('MaterialCanvass')->get();
                    
        $material_item_ids      = [];
        $component_item_ids     = [];

        foreach($materialQuantityRequestItems as $item){
            $material_item_ids[]     = $item->material_item_id;
            $component_item_ids[]    = $item->component_item_id; 
        }

        $component_items    = ComponentItem::whereIn('id',$component_item_ids)->get();
        $material_items     = DB::table('material_items')->whereIn('id',$material_item_ids)->get();

        $component_item_arr = [];

        foreach($component_items as $ci){
            $component_item_arr[$ci->id] = $ci;
        }

        $material_item_arr = [];

        foreach($material_items as $mi){
            $material_item_arr[$mi->id] = $mi;
        }


        $purchase_options               = [];
        $supplier_options               = [];
        $payment_terms_options          = [];

        foreach($materialQuantityRequestItems as $item){

            foreach($item->MaterialCanvass as $mcItem){

                if($mcItem->status != 'APRV'){
                    continue;
                }

                //To be filled up later with rows
                $supplier_options[$mcItem->supplier_id]         = [];
                $payment_terms_options[$mcItem->payment_term_id] = [];

                if(!isset($purchase_options[$mcItem->supplier_id])){
                    $purchase_options[$mcItem->supplier_id] = [];
                }

                if(!isset( $purchase_options[$mcItem->supplier_id][$mcItem->payment_term_id] )){
                    $purchase_options[$mcItem->supplier_id][$mcItem->payment_term_id] = [];
                }

                if(!isset( $purchase_options[$mcItem->supplier_id][$mcItem->payment_term_id][$item->component_item_id] )){
                    $purchase_options[$mcItem->supplier_id][$mcItem->payment_term_id][$item->component_item_id] = [];
                }
                
                $material_item_id = $item->material_item_id;

                $purchase_options[$mcItem->supplier_id][$mcItem->payment_term_id][$item->component_item_id][] = [
                    'material_quantity_request_item_id' => $item->id,
                    'material_canvass_id'               => $mcItem->id,
                    'material_item'                     => $material_item_arr[ $material_item_id],
                    'price'                             => $mcItem->price,
                    'requested_quantity'                => $item->requested_quantity
                ];
            }
        }

        $suppliers      = Supplier::whereIn('id',array_keys($supplier_options))->where('deleted_at',null)->get();
        $payment_terms  = PaymentTerm::toOptions(array_keys($payment_terms_options));


        foreach($suppliers as $row){

            $supplier_options[$row->id] = $row;
        }

        foreach($payment_terms as $row){
            
            $row = (object) $row;

            $payment_terms_options[ $row->id ] = $row;
        }

        return view('purchase_order/create',[
            'material_quantity_request'         => $materialQuantityRequest,
            'project'                           => $project,
            'section'                           => $section,
            'contract_item'                     => $contract_item,
            'component'                         => $component,
            'materialQuantityRequestItems'      => $materialQuantityRequestItems,
            'component_item_arr'                => $component_item_arr,
            
            'payment_terms_options'     => $payment_terms_options,
            'supplier_options'          => $supplier_options,
            'purchase_options'          => $purchase_options
        ]);
    }

    public function _create(Request $request){

        if(!$this->hasAccess(['purchase_order:own:create'])){
            return response()->json([
                'status'    => 0,
                'message'   => 'Access Denied',
                'data'      => []
            ]);
        }

        $material_quantity_request_id = (int) $request->input('material_quantity_request_id');
        $supplier_id                  = (int) $request->input('supplier_id');
        $payment_term_id              = (int) $request->input('payment_term_id');
        $project_id                   = (int) $request->input('project_id');
        $section_id                   = (int) $request->input('section_id');
        $contract_item_id             = (int) $request->input('contract_item_id');
        $component_id                 = (int) $request->input('component_id');
                    

        try{
            
            $po_items   = json_decode($request->input('items'),true);
            $extras     = json_decode($request->input('extras'),true);

            if(is_null($po_items) || is_null($extras)){
                throw('Error');
            }

        } catch (\Exception $e) {

            return response()->json([
                'status'    => 0,
                'message'   => $e->getMessage(),
                'data'      => []
            ]);
        }

        $itemsValidation = Validator::make(['items'=>$po_items],[
            'items.*.component_item_id' => [
                'required',
                'integer'
            ],
            'items.*.material_quantity_request_item_id' => [
                'required',
                'integer'
            ],
            'items.*.material_canvass_id' => [
                'required',
                'integer'
            ],
            'items.*.material_item_id' => [
                'required',
                'integer'
            ],
            'items.*.order_quantity' => [
                'required',
                'numeric'
            ],
            'items.*.price' => [
                'required',
                'decimal:2'
            ]
        ]);

        if ($itemsValidation->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $itemsValidation->messages()
            ]);
        }


        $extrasValidation = Validator::make(['extras'=>$extras],[
            'extras.*.text' => [
                'required',
                'string',
                'max:255'
            ],
            'extras.*.value' => [
                'required',
                'decimal:2'
            ]
        ]);

        if ($extrasValidation->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $extrasValidation->messages()
            ]);
        }

        $validation = Validator::make($request->all(),[
            'supplier_id' => [
                'integer',
                'required'
            ],
            'project_id' => [
                    'integer',
                'required'
            ],
            'section_id' => [
                'integer',
                'required'
            ],
            'contract_item_id'=>[
                'integer',
                'required'
            ],
            'component_id' => [
                'integer',
                'required'
            ],
            'payment_term_id' => [
                'required',
                'integer'
            ],
            'material_quantity_request_id' => [
                'required',
                'integer'
            ]
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validation->messages()
            ]);
        }

        //Check for duplicate material ID in the same component
        $duplicateChecker   = [];
        $duplicateFlag      = false;

        foreach($po_items as $item){

            if(!isset($duplicateChecker[ $item['component_item_id'] ])){
                $duplicateChecker[ $item['component_item_id'] ] = [];
            }

            $check = in_array($item['material_item_id'], $duplicateChecker[ $item['component_item_id'] ]);

            if($check){
                $duplicateFlag = true;
            }

            $duplicateChecker[ $item['component_item_id'] ][] = $item['material_item_id'];

            //TODO make this two query into one or turn into a transaction possible race condition
            //Check if po item is within budget
            $total_ordered = PurchaseOrderItem::where('material_item_id',$item['material_item_id'])
            ->where('material_quantity_request_item_id',$item['material_quantity_request_item_id'])
            ->where('status','APRV')
            ->sum('quantity');

            $mqri = MaterialQuantityRequestItem::find($item['material_quantity_request_item_id']);

            //Check for over budget
            if( ( $total_ordered + $item['order_quantity'] ) > $mqri->requested_quantity){

                return response()->json([
                    'status'    => 0,
                    'message'   => 'Order request is over budget',
                    'data'      => []
                ]);
            }
        }

        if ($duplicateFlag) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Duplicate materials detected on the same component item',
                'data'      => []
            ]);
        }


        $user_id = Auth::user()->id;

        DB::beginTransaction();

        $purchaseOrder = new PurchaseOrder();

        try {  

            $purchaseOrder->status                          = 'DRFT';
            $purchaseOrder->project_id                      = $project_id;
            $purchaseOrder->section_id                      = $section_id;
            $purchaseOrder->contract_item_id                = $contract_item_id;
            $purchaseOrder->component_id                    = $component_id;
            $purchaseOrder->supplier_id                     = $supplier_id;
            $purchaseOrder->payment_term_id                 = $payment_term_id;
            $purchaseOrder->material_quantity_request_id    = $material_quantity_request_id;
            $purchaseOrder->extras                          = json_encode($extras);
            $purchaseOrder->created_by                      = $user_id;

            $purchaseOrder-> save();

            $bulk = [];

            foreach($po_items as $item){
                $bulk[] = [
                    'purchase_order_id'                     => $purchaseOrder->id,
                    'status'                                => 'PEND',
                    'component_item_id'                     => $item['component_item_id'],
                    'material_quantity_request_item_id'     => $item['material_quantity_request_item_id'],
                    'material_canvass_id'                   => $item['material_canvass_id'],
                    'material_item_id'                      => $item['material_item_id'],
                    'quantity'                              => $item['order_quantity'],
                    'price'                                 => $item['price']
                ];
            }
            
            PurchaseOrderItem::insert($bulk);
            
            

            DB::commit();
        
        }catch(\Exception $e){

            return response()->json([
                'status'    => 0,
                'message'   => $e->getMessage(),
                'data'      => []
            ]);

            DB::rollback();

            return false;
        
        }

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => ['id'=>$purchaseOrder->id]
        ]);

    }

    public function _submit_for_review(Request $request){
        $id = (int) $request->input('id');

        $purchase_order = PurchaseOrder::find($id);

        if(!$purchase_order){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }


        
        $user = auth()->user();

        if(!$this->hasAccess(['purchase_order:all:update'])){

            if( !$this->hasAccess(['purchase_order:own:update']) ){
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Access Denied',
                    'data'      => []
                ]);
            }

            if($purchase_order->created_by != $user->id){
                 return response()->json([
                    'status'    => 0,
                    'message'   => 'Access Denied',
                    'data'      => []
                ]);
            }
        }


        if($purchase_order->status != 'DRFT'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record is not in status: DRFT',
                'data'      => []
            ]);
        }

        $purchase_order->status     = 'PEND';
        $purchase_order->updated_by = $user->id; 

        $purchase_order->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }

    public function _delete(Request $request){


        $id = (int) $request->input('id');

        $purchaseOrder = PurchaseOrder::find($id);

        if(!$purchaseOrder){

            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }

        $user = auth()->user();

        if(!$this->hasAccess(['purchase_order:all:delete'])){

            if( !$this->hasAccess(['purchase_order:own:delete']) ){
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Access Denied',
                    'data'      => []
                ]);
            }

            if($purchaseOrder->created_by != $user->id){
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Access Denied',
                    'data'      => []
                ]);
            }
        }

        if($purchaseOrder->status != 'PEND' && $purchaseOrder->status != 'DRFT'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record cannot be deleted, status is not pending or draft',
                'data'      => []
            ]);
        }

        DB::beginTransaction();

        try {  

            
            $purchaseOrder->forceDelete();

            PurchaseOrderItem::where('purchase_order_id', $id)->forceDelete();
        

            DB::commit();
        
            return response()->json([
                'status' => 1,
                'message' => '',
                'data' =>  []
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
    
    public function __print($id){

        $purchaseOrder           = PurchaseOrder::findOrFail($id);
        $materialQuantityRequest = MaterialQuantityRequest::findOrFail($purchaseOrder->material_quantity_request_id);

        $user = auth()->user();

        if(!$this->hasAccess(['purchase_order:all:view'])){

            if( !$this->hasAccess(['purchase_order:own:view']) ){
                return view('access_denied');
            }

            if($purchaseOrder->created_by != $user->id){
                return view('access_denied');
            }
        }

        if($purchaseOrder->status != 'APRV'){
            return abort(404);
        }

        $project                = $materialQuantityRequest->Project;
        $section                = $materialQuantityRequest->Section;
        $component              = $materialQuantityRequest->Component;
        
        $componentItems                 = $component->ComponentItems;
        $paymentTerm                    = $purchaseOrder->PaymentTerm;
        $supplier                       = $purchaseOrder->Supplier;
        $materialQuantityRequestItems   = $purchaseOrder->Items;
                            
        $material_id_arr                = [];

        foreach($materialQuantityRequestItems as $item){

            $material_id_arr[] = $item->material_item_id;
        }
                   

        $materialItems              = MaterialItem::whereIn('id',$material_id_arr)->get();
        $materialItemArr    =        [];


        foreach($materialItems as $materialItem){
            $materialItemArr[$materialItem->id] = $materialItem;
        }

        $extras = json_decode($purchaseOrder->extras);
        
    
        $html = view('purchase_order/print',[
            'purchase_order'                    => $purchaseOrder,
            'material_quantity_request'         => $materialQuantityRequest,
            'project'                           => $project,
            'section'                           => $section,
            'component'                         => $component,
            'supplier'                          => $supplier,
            'payment_term'                      => $paymentTerm,
            'items'                             => $materialQuantityRequestItems,
            'extras'                            => $extras,
            'materialItemArr'                   => $materialItemArr,
            'current_datetime'                  => Carbon::now()
            
        ])->render();
        
        $html2pdf = new Html2Pdf('P','A4','en', false, 'UTF-8', [5, 5, 10, 0]);
           

        try {
            $html2pdf->writeHTML($html);
            $html2pdf->output('Purchase Order - '.str_pad($purchaseOrder->id,0,6,STR_PAD_LEFT ).'.pdf');
            $html2pdf->clean();
        
        }catch(Html2PdfException $e) {
            $html2pdf->clean();
        
            $formatter = new ExceptionFormatter($e);
            echo $html;
            echo $formatter->getHtmlMessage();        
        } 
        

    }


    public function print($id){

        $purchaseOrder           = PurchaseOrder::findOrFail($id);
        $materialQuantityRequest = MaterialQuantityRequest::findOrFail($purchaseOrder->material_quantity_request_id);

        $user = auth()->user();

        if(!$this->hasAccess(['purchase_order:all:view'])){

            if( !$this->hasAccess(['purchase_order:own:view']) ){
                return view('access_denied');
            }

            if($purchaseOrder->created_by != $user->id){
                return view('access_denied');
            }
        }

        if($purchaseOrder->status != 'APRV'){
            return abort(404);
        }

        $project                = $materialQuantityRequest->Project;
        $contract_item          = $materialQuantityRequest->ContractItem;
        $section                = $materialQuantityRequest->Section;
        $component              = $materialQuantityRequest->Component;
        $date_needed            = $materialQuantityRequest->date_needed;
       
        $componentItems                 = $component->ComponentItems;
        $paymentTerm                    = $purchaseOrder->PaymentTerm;
        $supplier                       = $purchaseOrder->Supplier;
        $materialQuantityRequestItems   = $purchaseOrder->Items;
                            
        $material_id_arr                = [];

        foreach($materialQuantityRequestItems as $item){

            $material_id_arr[] = $item->material_item_id;
        }
                   

        $materialItems      = MaterialItem::whereIn('id',$material_id_arr)->get();
        $materialItemArr    =        [];


        foreach($materialItems as $materialItem){
            $materialItemArr[$materialItem->id] = $materialItem;
        }

        $extras = json_decode($purchaseOrder->extras);
        
        

        return view('purchase_order/print',[
            'purchase_order'                    => $purchaseOrder,
            'material_quantity_request'         => $materialQuantityRequest,
            'project'                           => $project,
            'section'                           => $section,
            'contract_item'                     => $contract_item,
            'component'                         => $component,
            'supplier'                          => $supplier,
            'payment_term'                      => $paymentTerm,
            'items'                             => $materialQuantityRequestItems,
            'extras'                            => $extras,
            'materialItemArr'                   => $materialItemArr,
            'date_needed'                       => $date_needed,
            'current_datetime'                  => Carbon::now()
            
        ]);
            
    }
    
    public function total_ordered(Request $request){

        //todo check role 

        $material_item_id                   = (int) $request->input('material_item_id');
        $material_quantity_request_item_id  = (int) $request->input('material_quantity_request_item_id');

        $total = PurchaseOrderItem::where('material_item_id',$material_item_id)
        ->where('material_quantity_request_item_id',$material_quantity_request_item_id)
        ->where('status','APRV')
        ->sum('quantity');

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'total_ordered'  => $total
            ]
        ]);

    }

    public function _request_void(Request $request){

        //todo check role

        $id = (int) $request->input('id');

        $purchaseOrder = PurchaseOrder::find($id);

        if(!$purchaseOrder){

            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }

        $user = auth()->user();
        
        if(!$this->hasAccess(['purchase_order:all:request_void'])){

            if( !$this->hasAccess(['purchase_order:own:request_void']) ){
                 return response()->json([
                    'status'    => 0,
                    'message'   => 'Access Denied',
                    'data'      => []
                ]);
            }

            if($purchaseOrder->created_by != $user->id){
                 return response()->json([
                    'status'    => 0,
                    'message'   => 'Access Denied',
                    'data'      => []
                ]);
            }
        }

        if($purchaseOrder->status != 'APRV'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Cannot request void, record has wrong status',
                'data'      => []
            ]);
        }

        $user_id = Auth::user()->id;

        DB::beginTransaction();

        try {  

            $purchaseOrder->approved_by = $user_id;
            $purchaseOrder->status      = 'REVO';
            $purchaseOrder->approved_at = Carbon::now();
            
            $purchaseOrder->save();
            
            // Don't change item status because it is only a request
            // DB::table('purchase_order_items')->where('purchase_order_id',$purchaseOrder->id)
            // ->update([
            //     'status' => 'REVO'
            // ]);

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
