<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Section;
use App\Models\Component;
use App\Models\MaterialQuantityRequest;
use App\Models\MaterialQuantityRequestItem;
use App\Models\MaterialQuantity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use App\Models\Unit;

class MaterialQuantityRequestController extends Controller
{
    
    public function selectCreate(){

        $projects = Project::where('status','=','ACTV')->orderBy('name','ASC')->get();

        return view('material_quantity_request/select',[
            'projects' => $projects
        ]);
    }

    

    public function create($project_id,$section_id,$component_id){
        
        $project_id = (int) $project_id;
        $project = Project::findOrFail($project_id);

        //If the project is not active then do not allow
        if($project->status != 'ACTV'){
            return view('material_quantity_request/unavailable',[
                'project'   => $project,
                'section'   => $section,
                'component' => $component
            ]);
        }

        $section_id = (int) $section_id;
        $section = Section::findOrFail($section_id);

        $component_id = (int) $component_id;
        $component = Component::findOrFail($component_id);

        //If the component is not approved then do not allow
        if($component->status != 'APRV'){
            return view('material_quantity_request/unavailable',[
                'project'   => $project,
                'section'   => $section,
                'component' => $component
            ]);
        }

        $component_item_ids     = [];
        $componentItem_options  = [];

        foreach($component->ComponentItems as $componentItem){
            $component_item_ids[] = $componentItem->id;

            
            $componentItem_options[$componentItem->id] = [
                'value'                 => $componentItem->id,
                'text'                  => $componentItem->name,
                'unit_id'               => $componentItem->unit_id,
                'quantity'              => $componentItem->quantity
            ];
        }
        
        $material_item_result = DB::table('material_quantities')
        ->whereIn('component_item_id',$component_item_ids)
        ->join('material_items','material_quantities.material_item_id','=','material_items.id')
        ->get();

        if(!count($material_item_result)){
            return abort(404);
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
            'project'               => $project,
            'section'               => $section,
            'component'             => $component,
            'material_options'      => $material_options,
            'componentItem_options' => $componentItem_options,
            'unit_options'          => $unit_options
        ]);
    }


    public function _create(Request $request){

        $project_id     = (int) $request->input('project_id');
        $section_id     = (int) $request->input('section_id');
        $component_id   = (int) $request->input('component_id');
        $description    = $request->input('description');
        $items          = $request->input('items');
        
       
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
                // Rule::exists('sections')->where(function (Builder $query) use ($project_id,$section_id) {
                //     return $query->where('id', $section_id)
                //     ->where('project_id',$project_id)
                //     ->where('deleted_at',null);
                // })
            ],
            'component_id' => [
                'required',
                'integer',
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

        $user_id    = Auth::user()->id;
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

        $doubleEntry = [];

        foreach($items as $item){

            $item['component_item_id']      = (int) $item['component_item_id'];
            $item['material_item_id']       = (int) $item['material_item_id'];


            //check for double entry
            $check = $item['component_item_id'].'-'.$item['material_item_id'];

            if(in_array($check,$doubleEntry)){

                return response()->json([
                    'status'    => 0,
                    'message'   => 'Error: Double entry with the same component item and material Item',
                    'data'      => []
                ]);

            }else{
                $doubleEntry[] = $check;
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
                    'message'   => 'Error: Material is out of budget',
                    'data'      => []
                ]);

            }
        }
        

        DB::beginTransaction();

        try {  

            $materialQuantityRequest = new MaterialQuantityRequest();

            $materialQuantityRequest->project_id    = $project_id;
            $materialQuantityRequest->section_id    = $section_id;
            $materialQuantityRequest->component_id  = $component_id;
            $materialQuantityRequest->description   = $description;
            $materialQuantityRequest->status        = 'PEND';
            $materialQuantityRequest->created_by    = $user_id;

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

        $project         = $materialQuantityRequest->Project;
        $section         = $materialQuantityRequest->Section;
        $component       = $materialQuantityRequest->Component;
        $request_items   = $materialQuantityRequest->Items;

        $component_item_ids     = [];
        $componentItem_options  = [];

        foreach($component->ComponentItems as $componentItem){
            $component_item_ids[] = $componentItem->id;

            $componentItem_options[$componentItem->id] = [
                'value'                  => $componentItem->id,
                'text'                   => $componentItem->name,
                'unit_id'      => $componentItem->unit_id,
                'quantity'               => $componentItem->quantity
            ];
        }
        
        $material_item_result = DB::table('material_quantities')->whereIn('component_item_id',$component_item_ids)
        ->join('material_items','material_quantities.material_item_id','=','material_items.id')
        ->get();

        $material_options = [];

        foreach($material_item_result as $row){

            if(!isset($material_options[$row->component_item_id])){
                $material_options[$row->component_item_id] = [];
            }

            $material_options[$row->component_item_id][$row->id] = [
                'value'         => $row->material_item_id,
                'text'          => trim($row->name.' '.$row->specification_unit_packaging.' '.$row->brand),
                'equivalent'    => $row->equivalent,
                'quantity'      => $row->quantity
            ];
        }


        
        $unit_options = Unit::toOptions();

        return view('material_quantity_request/display',[
            'project'                   => $project,
            'section'                   => $section,
            'component'                 => $component,
            'material_quantity_request' => $materialQuantityRequest,
            'request_items'             => $request_items,
            'material_options'          => $material_options,
            'componentItem_options'     => $componentItem_options,
            'unit_options'              => $unit_options
        ]);
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
                'message'   => 'Error: This record can no longer be updated (Status: '.$materialQuantityRequest->status.')',
                'data'      => []
            ]);
        }

        if($materialQuantityRequest->project()->status != 'ACTV'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Error: This record can no longer be updated, because the project status is not active',
                'data'      => []
            ]);
        }

        if($materialQuantityRequest->component()->status != 'APRV'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Error: This record can no longer be updated, because the component status is not approved',
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

        $doubleEntry = [];

        foreach($items as $item){

            $item['component_item_id']      = (int) $item['component_item_id'];
            $item['material_item_id']       = (int) $item['material_item_id'];


            //check for double entry
            $check = $item['component_item_id'].'-'.$item['material_item_id'];

            if(in_array($check,$doubleEntry)){

                return response()->json([
                    'status'    => 0,
                    'message'   => 'Error: Double entry with the same component item and material Item',
                    'data'      => []
                ]);

            }else{
                $doubleEntry[] = $check;
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
                    'message'   => 'Error: Material is out of budget',
                    'data'      => []
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
        $component_id       = (int) $request->input('component_id')  ?? 0;
        $query              = (int) $request->input('query')    ?? 0;
        $status             = $request->input('status')    ?? '';
        $orderBy            = $request->input('order_by')       ?? 'id';
        $order              = $request->input('order')          ?? 'DESC';
        $result             = [];

        $materialQuantityRequest = new MaterialQuantityRequest();

        $user_id = Auth::user()->id;
        $materialQuantityRequest = $materialQuantityRequest->where('created_by','=',$user_id);
        
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
            
            $result = $materialQuantityRequest->orderBy($orderBy,$order)->skip($page)->take($limit)->with('Project')->with('Section')->with('Component')->with('User')->get();
            
        }else{

            $result = $materialQuantityRequest->orderBy($orderBy,$order)->take($limit)->with('Project')->with('Section')->with('Component')->with('User')->get();
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

        $requested_quantity = MaterialQuantityRequestItem::where('status','=','APRV')
        ->where('component_item_id','=',$component_item_id)
        ->where('material_item_id','=',$material_item_id)
        ->sum('requested_quantity');
        
        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> [
                'total_requested' => $requested_quantity
            ]
        ]);
    }
}
