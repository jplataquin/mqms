<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Section;
use App\Models\ContractItem;
use App\Models\Component;
use App\Models\PurchaseOrder;
use App\Models\MaterialQuantityRequest;
use App\Models\MaterialQuantityRequestItem;
use App\Models\MaterialQuantity;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use App\Models\Unit;
use Carbon\Carbon;
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;


class MaterialQuantityRequestController extends Controller
{
    
    public function selectCreate(){

        $projects = Project::where('status','=','ACTV')->orderBy('name','ASC')->get();

        return view('material_quantity_request/select',[
            'projects' => $projects
        ]);
    }

    

    public function create($project_id,$section_id,$contract_item_id,$component_id){
        
        $project_id = (int) $project_id;
        $project    = Project::findOrFail($project_id);


        $section_id = (int) $section_id;
        $section = Section::findOrFail($section_id);

        $contract_item_id = (int) $contract_item_id;
        $contract_item = ContractItem::findOrFail($contract_item_id);

        $component_id = (int) $component_id;
        $component = Component::findOrFail($component_id);

        //If the project is not active then do not allow
        if($project->status != 'ACTV'){
            return view('material_quantity_request/unavailable',[
                'project'       => $project,
                'section'       => $section,
                'contract_item' => $contract_item,
                'component'     => $component,
                'message'       => 'Project status is not approved'
            ]);
        }

        //If the component is not approved then do not allow
        if($component->status != 'APRV'){
            return view('material_quantity_request/unavailable',[
                'project'       => $project,
                'section'       => $section,
                'component'     => $component,
                'contract_item' => $contract_item,
                'message'       => 'Component status is not approved'
            ]);
        }

        $component_item_ids      = [];
        $component_item_options  = [];

        foreach($component->ComponentItems as $component_item){

            //Skip soft deleted
            if($component_item->deleted_at != null) continue;

            $component_item_ids[] = $component_item->id;

            
            $component_item_options[$component_item->id] = [
                'value'                 => $component_item->id,
                'text'                  => $component_item->name,
                'unit_id'               => $component_item->unit_id,
                'quantity'              => $component_item->quantity
            ];
        }

        //Query material quantities of the component item
        $material_item_result = DB::table('material_quantities')
        ->whereIn('component_item_id',$component_item_ids)
        ->join('material_items','material_quantities.material_item_id','=','material_items.id')
        ->get();

        //If not materials quantities are found inform the user that they cannot request
        if(!count($material_item_result)){
            return view('material_quantity_request/unavailable',[
                'project'       => $project,
                'section'       => $section,
                'component'     => $component,
                'contract_item' => $contract_item,
                'message'       => 'There are no material quantities maintained in any of the component items'
            ]);
        };

        $material_options = [];

        foreach($material_item_result as $row){

            if(!isset($material_options[$row->component_item_id])){
                $material_options[$row->component_item_id] = [];
            }

            $material_options[$row->component_item_id][$row->id] = [
                'value'         => $row->material_item_id,
                'text'          => trim($row->brand.' '.$row->name.' '.$row->specification_unit_packaging),
                'equivalent'    => $row->equivalent,
                'quantity'      => $row->quantity
            ];
        }

        $unit_options = Unit::toOptions();

        return view('material_quantity_request/create',[
            'project'                   => $project,
            'section'                   => $section,
            'component'                 => $component,
            'contract_item'             => $contract_item,
            'material_options'          => $material_options,
            'component_item_options'    => $component_item_options,
            'unit_options'              => $unit_options
        ]);
    }


    public function _create(Request $request){

        $codes = [
            'material_request:own:create'
        ];

        $user = auth()->user();

        if(!$this->hasAaccess($codes)){
            
            return response()->json([
                'status'    => 0,
                'message'   => 'Resitricted action, no permission(s) granted',
                'data'      => [
                    'user'                      => $user,
                    'required_access_codes'     => $codes,
                    'currrent_access_codes'     => $this->accessCodes
                ]
            ]);
            
        }

        $project_id         = (int) $request->input('project_id');
        $section_id         = (int) $request->input('section_id');
        $contract_item_id   = (int) $request->input('contract_item_id'); 
        $component_id       = (int) $request->input('component_id');
        $description        = $request->input('description');
        $items              = $request->input('items');
        
       
        $validator = Validator::make($request->all(),[
            'project_id' => [
                'required',
                'integer',
                // Rule::exists('projects')->where(function (Builder $query) use ($project_id) {
                //     return $query->where('id', $project_id)->where('deleted_at',null);
                // })
            ],
            'section_id' => [
                'required',
                'integer',
                'gte:1'
                // Rule::exists('sections')->where(function (Builder $query) use ($project_id,$section_id) {
                //     return $query->where('id', $section_id)
                //     ->where('project_id',$project_id)
                //     ->where('deleted_at',null);
                // })
            ],
            'contract_item_id' =>[
                'required',
                'integer',
                'gte:1'
            ],
            'component_id' => [
                'required',
                'integer',
                'gte:1'
                // Rule::exists('components')->where(function (Builder $query) use ($section_id,$component_id) {
                //     return $query->where('id', $component_id)
                //     ->where('section_id',$section_id)
                //     ->where('deleted_at',null);
                // })
            ],
            'description' => [
                'required'
            ],
            'items' =>[
                'required',
                'json'
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }


        $project = Project::find($project_id);

        //If the project does not exist or is not active then do not allow
        if($project->status != 'ACTV'){
            
            return response()->json([
                'status'    => 0,
                'message'   => 'Error: Project status is not active',
                'data'      => []
            ]);
        }

        $component = Component::find($component_id);

        //If the component does not exist or is not approved then do not allow
        if($component->status != 'APRV'){
            
            return response()->json([
                'status'    => 0,
                'message'   => 'Error: Component status is not approved',
                'data'      => []
            ]);
        }

        $user_id    = $user->idl;
        $items      = json_decode($items,true);

        if(!count($items)){
            return response()->json([
                'status'    => 0,
                'message'   => 'At least one item is requireds',
                'data'      => []
            ]);
        }

        
        $itemValidator = Validator::make(['items'=> $items],[
            'items.*.component_item_id' => [
                'required',
                'integer',
                'gte:1'
            ],
            'items.*.material_item_id' => [
                'required',
                'integer',
                'gte:1'
            ],
            'items.*.requested_quantity' =>[
                'required',
                'numeric',
                'gt:0'
            ]
        ]);

        if ($itemValidator->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $itemValidator->messages()
            ]);
        }

        $doubleEntry         = [];
        $doubleComponentItem = [];

        foreach($items as $item){

            $item['component_item_id']      = (int) $item['component_item_id'];
            $item['material_item_id']       = (int) $item['material_item_id'];


            //check for double entry
            $check_combination = $item['component_item_id'].'-'.$item['material_item_id'];

            if( in_array($check_combination,$doubleEntry) ){

                return response()->json([
                    'status'    => 0,
                    'message'   => 'Double entry with the same component item and material item',
                    'data'      => []
                ]);

            }else{
                $doubleEntry[] = $check_combination;
            }

            if( in_array($item['component_item_id'],$doubleComponentItem) ){
                
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Cannot have two or more entries with the same component item',
                    'data'      => []
                ]);

            }else{
                $doubleComponentItem[] = $item['component_item_id'];
            }
        }


        //Validate that the request is still within budget 
        foreach($items as $item){

            $item['component_item_id']     = (int) $item['component_item_id'];
            $item['material_item_id']      = (int) $item['material_item_id'];
            $item['requested_quantity']    = (float) $item['requested_quantity'];

            $requested_quantity_total = MaterialQuantityRequestItem::where('status','=','APRV')
            ->where('component_item_id','=',$item['component_item_id'])
            ->where('material_item_id','=',$item['material_item_id'])
            ->sum('requested_quantity');
            
            $materialQuantity = MaterialQuantity::where('component_item_id','=',$item['component_item_id'])
            ->where('material_item_id','=',$item['material_item_id'])->first();
           
            $remaining = $materialQuantity->quantity - $requested_quantity_total;


            if($remaining < $item['requested_quantity']){

                return response()->json([
                    'status'    => 0,
                    'message'   => 'Out of budget',
                    'data'      => []
                ]);

            }
        }
        

        DB::beginTransaction();

        try {  

            $materialQuantityRequest = new MaterialQuantityRequest();

            $materialQuantityRequest->project_id        = $project_id;
            $materialQuantityRequest->section_id        = $section_id;
            $materialQuantityRequest->contract_item_id  = $contract_item_id;
            $materialQuantityRequest->component_id      = $component_id;
            $materialQuantityRequest->description       = $description;
            $materialQuantityRequest->status            = 'PEND';
            $materialQuantityRequest->created_by        = $user_id;

            $materialQuantityRequest->save();

            foreach($items as $item){

                $materialQuantityRequestItem = new MaterialQuantityRequestItem();

                $materialQuantityRequestItem->material_quantity_request_id = $materialQuantityRequest->id;
                $materialQuantityRequestItem->component_item_id            = $item['component_item_id'];
                $materialQuantityRequestItem->material_item_id             = $item['material_item_id'];
                $materialQuantityRequestItem->requested_quantity           = $item['requested_quantity'];
                
                $materialQuantityRequestItem->save();
            }
            
            
            DB::commit();

            return response()->json([
                'status'    => 1,
                'message'   => '',
                'data'      => [
                    'id' => $materialQuantityRequest->id
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


    public function display($id){
        


        $id = (int) $id;

        $materialQuantityRequest = MaterialQuantityRequest::findOrFail($id);
        
        $user = auth()->user();

        //Check if current user does not own the record, then are they allowed to view it?
        $codes = ['material_request:all:view'];

        if($materialQuantityRequest->created_by != $user->id && !$this->hasAccess($codes)){
            return view('permission_denied',[
                'user'                      => $user,
                'required_access_codes'     => $codes,
                'current_access_codes'      => $this->accessCodes
            ]);
        }

        $project         = $materialQuantityRequest->Project;
        $section         = $materialQuantityRequest->Section;
        $contract_item   = $materialQuantityRequest->ContractItem;
        $component       = $materialQuantityRequest->Component;
        $request_items   = $materialQuantityRequest->Items;

        $component_item_ids     = [];
        $component_item_options  = [];

        foreach($component->ComponentItems as $componentItem){
            $component_item_ids[] = $componentItem->id;

            $component_item_options[$componentItem->id] = [
                'value'                  => $componentItem->id,
                'text'                   => $componentItem->name,
                'unit_id'                => $componentItem->unit_id,
                'quantity'               => $componentItem->quantity
            ];
        }
        
        $material_item_result = DB::table('material_quantities')->whereIn('component_item_id',$component_item_ids)
        ->join('material_items','material_quantities.material_item_id','=','material_items.id')
        ->select(
            'material_quantities.material_item_id AS material_item_id',
            'material_quantities.component_item_id AS component_item_id',
            'material_quantities.quantity AS quantity',
            'material_quantities.equivalent AS equivalent',
            'material_quantities.deleted_at AS deleted_at',
            'material_items.name AS name',
            'material_items.specification_unit_packaging AS specification_unit_packaging',
            'material_items.brand AS brand'
        )
        ->get();

        $material_options = [];

        foreach($material_item_result as $row){

            if(!isset($material_options[$row->component_item_id])){
                $material_options[$row->component_item_id] = [];
            }

            $material_options[$row->component_item_id][$row->material_item_id] = [
                'value'         => $row->material_item_id,
                'text'          => trim($row->name.' '.$row->specification_unit_packaging.' '.$row->brand),
                'equivalent'    => $row->equivalent,
                'quantity'      => $row->quantity,
                'deleted_flag'  => ($row->deleted_at) ? true : false
            ];
        }


        
        $unit_options = Unit::toOptions();

        return view('material_quantity_request/display',[
            'project'                   => $project,
            'section'                   => $section,
            'contract_item'             => $contract_item,
            'component'                 => $component,
            'material_quantity_request' => $materialQuantityRequest,
            'request_items'             => $request_items,
            'material_options'          => $material_options,
            'component_item_options'    => $component_item_options,
            'unit_options'              => $unit_options
        ]);
    }

    public function print($id){
        
        $id = (int) $id;

        $materialQuantityRequest = MaterialQuantityRequest::findOrFail($id);

        $project         = $materialQuantityRequest->Project;
        $section         = $materialQuantityRequest->Section;
        $contract_item   = $materialQuantityRequest->ContractItem;
        $component       = $materialQuantityRequest->Component;
        $request_items   = $materialQuantityRequest->Items;

        $component_item_ids     = [];
        $component_item_options  = [];

        //Arrange Component Items
        foreach($component->ComponentItems as $componentItem){

            $component_item_options[$componentItem->id] = (object) [
                'value'        => $componentItem->id,
                'text'         => $componentItem->name,
                'unit_id'      => $componentItem->unit_id,
                'quantity'     => $componentItem->quantity
            ];
        }

        //Arrange request items for easier query
        $request_item_arr = [];
        $request_item_ids = [];
        foreach($request_items as $rq){

            $component_item_ids[] = $rq->component_item_id;

            $request_item_arr[$materialQuantityRequest->id.'-'.$rq->component_item_id.'-'.$rq->material_item_id] = $rq;
            $request_item_ids[] = $rq->material_item_id;
        }

        //Get material_quantities that are not deleted
        $material_item_result = DB::table('material_quantities')
        ->where('material_quantities.deleted_at',null)
        ->whereIn('component_item_id',$component_item_ids)
        ->whereIn('material_quantities.material_item_id',$request_item_ids)
        ->join('material_items','material_quantities.material_item_id','=','material_items.id')
        ->select(
            'material_quantities.material_item_id AS material_item_id',
            'material_quantities.component_item_id AS component_item_id',
            'material_quantities.quantity AS quantity',
            'material_quantities.equivalent AS equivalent',
            'material_quantities.deleted_at AS deleted_at',
            'material_items.name AS name',
            'material_items.specification_unit_packaging AS specification_unit_packaging',
            'material_items.brand AS brand'
        )
        ->get();

        $item_options = [];

        foreach($material_item_result as $row){

            if(!isset($material_options[$row->component_item_id])){
                $item_options[$row->component_item_id] = [];
            }


            $item_options[$row->component_item_id][$row->material_item_id] = (object) [
                'value'                     => $row->material_item_id,
                'text'                      => trim($row->name.' '.$row->specification_unit_packaging.' '.$row->brand),
                'equivalent'                => $row->equivalent,
                'budget_quantity'           => $row->quantity,
                'approved_quantity'         => $this->get_total_approved_quantity(
                    $request_item_arr[$materialQuantityRequest->id.'-'.$row->component_item_id.'-'.$row->material_item_id]->id,
                    $row->component_item_id,
                    $row->material_item_id
                ),   
            ];
        }

       // exit;
        
        $unit_options = Unit::toOptions();

        $html = view('material_quantity_request/print',[
            'project'                   => $project,
            'section'                   => $section,
            'contract_item'             => $contract_item,
            'component'                 => $component,
            'material_quantity_request' => $materialQuantityRequest,
            'request_items'             => $request_items,
            'item_options'              => $item_options,
            'component_item_options'    => $component_item_options,
            'unit_options'              => $unit_options,
            'date_printed'              => Carbon::now()
        ])->render();


        $html2pdf = new Html2Pdf('L','A4','en', false, 'UTF-8', [5, 5, 10, 0]);
           

        try {
            $html2pdf->writeHTML($html);
            $html2pdf->output('Material Request - '.str_pad($materialQuantityRequest->id,0,6,STR_PAD_LEFT ).'.pdf');
            $html2pdf->clean();
        
        }catch(Html2PdfException $e) {
            $html2pdf->clean();
        
            $formatter = new ExceptionFormatter($e);
            echo $html;
            echo $formatter->getHtmlMessage();        
        }
    }

    public function _update(Request $request){

        $id             = (int) $request->input('id');
        $description    = $request->input('description');
        $items          = $request->input('items');
        $delete_items   = $request->input('delete_items');
        

        $validator = Validator::make($request->all(),[
            'id' => [
                'required',
                'integer'
            ],
            'description' => [
                'required'
            ],
            'items' =>[
                'required',
                'json'
            ],
            'delete_items' =>[
                'required',
                'json'
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }


        $materialQuantityRequest = MaterialQuantityRequest::find($id);

        if(!$materialQuantityRequest){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }

        if($materialQuantityRequest->status != 'PEND'){
            return response()->json([
                'status'    => 0,
                'message'   => 'This record can no longer be updated (Status: '.$materialQuantityRequest->status.')',
                'data'      => []
            ]);
        }

        if($materialQuantityRequest->Project->status != 'ACTV'){
            return response()->json([
                'status'    => 0,
                'message'   => 'This record can no longer be updated, because the project status is not active',
                'data'      => []
            ]);
        }

        if($materialQuantityRequest->Component->status != 'APRV'){
            return response()->json([
                'status'    => 0,
                'message'   => 'This record can no longer be updated, because the component status is not approved',
                'data'      => []
            ]);
        }

        $user_id        = Auth::user()->id;
        $items          = json_decode($items,true);
        $delete_items   = json_decode($delete_items);
         
        if(!count($items)){
            return response()->json([
                'status'    => 0,
                'message'   => 'At least one item is requireds',
                'data'      => []
            ]);
        }

        
        $itemValidator = Validator::make(['items'=> $items],[
            'items.*.id' => [
                'required',
                'integer'
            ],
            'items.*.component_item_id' => [
                'required',
                'integer'
            ],
            'items.*.material_item_id' => [
                'required',
                'integer'
            ],
            'items.*.requested_quantity' =>[
                'required',
                'numeric'
            ]
        ]);

        if ($itemValidator->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $itemValidator->messages()
            ]);
        }


        //Prepare existing items for checking of deleted items later
        $existing_items     = $materialQuantityRequest->Items;
        $existing_items_arr = [];

        foreach($existing_items as $ex_item){
            $existing_items_arr[] = $ex_item->id;
        }

        //Extra validation for dobule entry, deleted ites and po quantity validation
        $doubleEntry = [];
        $items_arr   = [];

        foreach($items as $item){

            $item['component_item_id']      = (int) $item['component_item_id'];
            $item['material_item_id']       = (int) $item['material_item_id'];
            $item['id']                     = (int) $item['id']; 

            $items_arr[] = $item['id'];


            //check for double entry
            $check = $item['component_item_id'].'-'.$item['material_item_id'];

            if(in_array($check,$doubleEntry)){

                return response()->json([
                    'status'    => 0,
                    'message'   => 'Double entry with the same component item and material Item',
                    'data'      => []
                ]);

            }else{
                $doubleEntry[] = $check;
            }
        }

        //TODO this requires testing
        //Validate deleted items
        foreach($existing_items_arr as $ex_id){

            //if existing entry is not in input and not in deleted haystack
            if(!in_array($ex_id,$items_arr) && !in_array($ex_id,$delete_items)){
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Unaccounted deleted entry',
                    'data'      => []
                ]);
            }
        }

        //PO quantity validation for input
        foreach($items as $item){
            $item['id']                    = (int) $item['id'];
            $item['requested_quantity']    = (float) $item['requested_quantity'];

            if(!$item['id']) continue;

            $po_result = $this->_get_total_po_quantity($item['id']);

            if($po_result['status'] <= 0){
                
                return response()->json([
                    'status'    => 0,
                    'message'   => 'PO quantity error',
                    'data'      => $po_result
                ]);
            }

            //Check if new requsted quantity is less than the maintained PO quantity
            if($item['requested_quantity'] < $po_result['data']['total']){
                return response()->json([
                    'status'    => 0,
                    'message'   => "An item entry quantity cannot be lower than what was already been PO'd",
                    'data'      => [
                        'item_id'               => $item['id'],
                        'requested_quantity'    => $item['requested_quantity'],
                        'po_quantity'           => $po_result['data']['total'] 
                    ]
                ]);
            }
        }

        //PO quantity validation for deleted items
        foreach($delete_items as $del_item_id){

            $del_item_id = (int) $del_item_id;

            $po_result = $this->_get_total_po_quantity($del_item_id);

            if($po_result['status'] <= 0){
                
                return response()->json([
                    'status'    => 0,
                    'message'   => 'PO quantity error',
                    'data'      => $po_result
                ]);
            }

            if($po_result['data']['total'] > 0){
                return response()->json([
                    'status'    => 0,
                    'message'   => "An item entry cannot be deleted because it has a maintained PO quantity",
                    'data'      => []
                ]);
            }
        }

        //Validate that the request is still within budget 
        foreach($items as $item){

            $item['component_item_id']     = (int) $item['component_item_id'];
            $item['material_item_id']      = (int) $item['material_item_id'];
            $item['requested_quantity']    = (float) $item['requested_quantity'];

            $requested_quantity_total = MaterialQuantityRequestItem::where('status','=','APRV')
            ->where('component_item_id','=',$item['component_item_id'])
            ->where('material_item_id','=',$item['material_item_id'])
            ->sum('requested_quantity');
            
            $materialQuantity = MaterialQuantity::where('component_item_id','=',$item['component_item_id'])
            ->where('material_item_id','=',$item['material_item_id'])->first();
           
            $remaining = $materialQuantity->quantity - $requested_quantity_total;


            if($remaining < $item['requested_quantity']){

                return response()->json([
                    'status'    => 0,
                    'message'   => 'Material is out of budget',
                    'data'      => [
                        'remaining' => $remaining,
                        'request' =>  $item['requested_quantity']
                    ]
                ]);

            }
        }

        DB::beginTransaction();

        try {  

            $materialQuantityRequest->description   = $description;
            $materialQuantityRequest->updated_by    = $user_id;

            $materialQuantityRequest->save();

            foreach($items as $item){

                //If new record
                if(!$item['id']){
                    $materialQuantityRequestItem = new MaterialQuantityRequestItem();

                    $materialQuantityRequestItem->material_quantity_request_id = $materialQuantityRequest->id;
                    $materialQuantityRequestItem->component_item_id            = $item['component_item_id'];
                    $materialQuantityRequestItem->material_item_id             = $item['material_item_id'];
                    $materialQuantityRequestItem->requested_quantity           = $item['requested_quantity'];
                    
                    $materialQuantityRequestItem->save();

                //If exiting record
                }else{
                    $MQRItem = MaterialQuantityRequestItem::find($item['id']);

                    if($MQRItem){
                        
                        if($MQRItem->material_quantity_request_id == $materialQuantityRequest->id){

                            $MQRItem->component_item_id        = $item['component_item_id'];
                            $MQRItem->material_item_id         = $item['material_item_id'];
                            $MQRItem->requested_quantity       = $item['requested_quantity'];
                            
                            $MQRItem->save();
                        }
                    }
                }
            }
            
            //Delete items that belong to the material quantity request
            if(count($delete_items)){
                MaterialQuantityRequestItem::where('material_quantity_request_id',$materialQuantityRequest->id)
                ->whereIn('id',$delete_items)->delete();
            }
            
            DB::commit();

            return response()->json([
                'status'    => 1,
                'message'   => '',
                'data'      => [
                    'id' => $materialQuantityRequest->id
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


    public function list(){

        $projects = Project::all();

        return view('material_quantity_request/list',[
            'projects' => $projects
        ]);
    }


    public function _list(Request $request){

        //todo check role

        $page               = (int) $request->input('page')     ?? 1;
        $limit              = (int) $request->input('limit')    ?? 10;
        $project_id         = (int) $request->input('project_id')  ?? 0;
        $section_id         = (int) $request->input('section_id')  ?? 0;
        $contract_item_id   = (int) $request->input('contract_item_id') ?? 0;
        $component_id       = (int) $request->input('component_id')  ?? 0;
    
        $query              = (int) $request->input('query')    ?? 0;
        $status             = $request->input('status')    ?? '';
        $orderBy            = $request->input('order_by')       ?? 'id';
        $order              = $request->input('order')          ?? 'DESC';
        $result             = [];

        $materialQuantityRequest = new MaterialQuantityRequest();

        $user_id = Auth::user()->id;
        

        //If current user has no permission to view all records only show records they own
        if(! $this->hasAccess(['material_request:all:view']) ){
            $materialQuantityRequest = $materialQuantityRequest->where('created_by','=',$user_id);
        }
    
        
        if($query){
            $materialQuantityRequest = $materialQuantityRequest->where('id','=',$query);
        }

        if($project_id){
            
            $materialQuantityRequest = $materialQuantityRequest->where('project_id','=',$project_id);

            if($section_id){
                $materialQuantityRequest = $materialQuantityRequest->where('section_id','=',$section_id);

                if($component_id){
                    $materialQuantityRequest = $materialQuantityRequest->where('component_id','=',$component_id);

                }
            }
        }

        if($status){
            $materialQuantityRequest = $materialQuantityRequest->where('status','=',$status);
        }

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $materialQuantityRequest->orderBy($orderBy,$order)->skip($page)->take($limit)->with('Project')->with('Section')->with('ContractItem')->with('Component')->with('User')->get();
            
        }else{

            $result = $materialQuantityRequest->orderBy($orderBy,$order)->take($limit)->with('Project')->with('Section')->with('ContractItem')->with('Component')->with('User')->get();
        }

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> $result
        ]);
    }

    public function _total_approved_quantity(Request $request){
        
        $component_item_id  = (int) $request->input('component_item_id');
        $material_item_id   = (int) $request->input('material_item_id');
        $material_quantity_request_item_id = (int) $request->input('material_quantity_request_item_id');

        $total_approved_quantity = $this->get_total_approved_quantity(
            $material_quantity_request_item_id,
            $component_item_id,
            $material_item_id
        );

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> [
                'total_approved_quantity' => $total_approved_quantity
            ]
        ]);
    }

    private function get_total_approved_quantity($material_quantity_request_item_id,$component_item_id,$material_item_id){

        
        $total_approved_quantity = 0;

        if($material_quantity_request_item_id){
            $total_approved_quantity = MaterialQuantityRequestItem::where('status','=','APRV')
            ->where('component_item_id','=',$component_item_id)
            ->where('material_item_id','=',$material_item_id)
            ->where('id','!=',$material_quantity_request_item_id)
            ->sum('requested_quantity');
        }else{
            $total_approved_quantity = MaterialQuantityRequestItem::where('status','=','APRV')
            ->where('component_item_id','=',$component_item_id)
            ->where('material_item_id','=',$material_item_id)
            ->sum('requested_quantity');
        }
        
        
        return $total_approved_quantity;
    }


    public function get_total_po_quantity(Request $request){
        
        $material_quantity_request_item_id  = (int) $request->input('material_quantity_request_item_id') ?? 0;
        

        $result = $this->_get_total_po_quantity($material_quantity_request_item_id);

        return response()->json($result);
    }

    public function _get_total_po_quantity($material_quantity_request_item_id){
        
        
        $material_quantity_request_item = MaterialQuantityRequestItem::find($material_quantity_request_item_id);


        if(!$material_quantity_request_item){
            return [
                'status' => 0,
                'message' => 'Record not found',
                'data' => [] 
            ];
        }

 
        $total = PurchaseOrderItem::where('material_item_id',$material_quantity_request_item->material_item_id)
        ->where('material_quantity_request_item_id',$material_quantity_request_item->id)
        ->where('status','!=','REJC')
        ->where('status','!=','VOID')
        ->sum('quantity');

        return [
            'status' => 1,
            'message' => '',
            'data' => [
                'total' => $total
            ]
        ];
    }

    public function _revert_to_pending(Request $request){

        $id = (int) $request->input('id') ?? 0;

        $material_quantity_request = MaterialQuantityRequest::find($id);

        if(!$material_quantity_request){
            return [
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ];
        }

        if($material_quantity_request->status != 'APRV'){
            return [
                'status'    => 0,
                'message'   => 'Material Request is not in Approved status',
                'data'      => []
            ];
        }

        $count_aprv_po = $material_quantity_request->PurchaseOrder()
        ->where('deleted_at',null)
        ->whereIn('status',['APRV','PEND'])->count();

        if($count_aprv_po){
            return [
                'status'    => 0,
                'message'   => 'Record dependency issue, this Material Request has (APRV,REJC) Purchase Order(s) that are dependent',
                'data'      => []
            ];
        }

        $user_id    = Auth::user()->id;

        DB::beginTransaction();

        try {  

            DB::table('material_quantity_request_items')
            ->where('material_quantity_request_id',$id)
            ->update([
                'status' => 'PEND'
            ]);

            $material_quantity_request->status      = 'PEND';
            $material_quantity_request->updated_by  = $user_id;
            $material_quantity_request->save();

            DB::commit();

            return response()->json([
                'status'    => 1,
                'message'   => '',
                'data'      => []
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


    public function po_list($id){

        $id = (int) $id;
        
        $material_request   = MaterialQuantityRequest::findOrFail($id);
        $project            = $material_request->Project;
        $section            = $material_request->Section;
        $contract_item      = $material_request->ContractItem;
        $component          = $material_request->Component;

        $po_list = PurchaseOrder::where('material_quantity_request_id',$material_request->id)
        ->orderBy('created_at','desc')
        ->get();

        $pending  = [];
        $approved = [];
        $rejected = [];
        $deleted  = [];

        foreach($po_list as $po){

            if($po->deleted_at != null){
                
                $deleted[] = $po;

            }else if($po->status == 'PEND'){

                $pending[] = $po;

            }else if($po->status == 'APRV'){
                $approved[] = $po;

            }else if($po->status == 'REJC'){
                
                $rejected[] = $po;
            }
        }

        return view('material_quantity_request/po_list',[
            'project'           => $project,
            'section'           => $section,
            'contract_item'     => $contract_item,
            'component'         => $component,
            'material_request'  => $material_request,
            'pending'           => $pending,
            'approved'          => $approved,
            'rejected'          => $rejected,
            'deleted'           => $deleted,
            'total_count'       => count($po_list)
        ]);
    }
}
