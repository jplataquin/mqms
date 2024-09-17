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
use App\Models\Supplier;
use App\Models\PaymentTerm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class MaterialCanvassReviewController extends Controller
{
    public function list(){

        $projects = Project::orderBy('name','ASC')->where('status','=','ACTV')->get();

        
        return view('review/material_canvass/list',[
            'projects' => $projects
        ]);
    }

 
 
    public function _list(Request $request){

        //todo check role

        $page            = (int) $request->input('page')     ?? 1;
        $limit           = (int) $request->input('limit')    ?? 10;
        $project_id      = (int) $request->input('project_id')  ?? 0;
        $section_id      = (int) $request->input('section_id')  ?? 0;
        $component_id    = (int) $request->input('component_id')  ?? 0;
        $query           = (int) $request->input('query')    ?? 0;
        $orderBy         = $request->input('order_by')       ?? 'id';
        $order           = $request->input('order')          ?? 'DESC';
        $result = [];

        $materialQuantityRequest = new MaterialQuantityRequest();

        $materialQuantityRequest = $materialQuantityRequest->where('status','=','APRV');
        
        
        $materialQuantityRequest = $materialQuantityRequest->whereIn('id',  function($query)
        {
            $query->select('material_canvass.material_quantity_request_id')
                  ->from('material_canvass')
                  ->where('material_canvass.status', '=', 'PEND')
                  ->where('material_canvass.deleted_at','=',null);
        });

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
    
    public function display($id){
        
        $materialQuantityRequest = MaterialQuantityRequest::findOrFail($id);

        if($materialQuantityRequest->status != 'APRV'){
            return abort(404);
        }

        $project                = $materialQuantityRequest->Project;
        $section                = $materialQuantityRequest->Section;
        $contract_item          = $materialQuantityRequest->ContractItem;
        $component              = $materialQuantityRequest->Component;
      
        if($project->status != 'APRV'){
            return abort(404);
        }

        if($component->status != 'APRV'){
            return abort(404);
        }

        $items                  = $materialQuantityRequest->Items()->with('MaterialCanvass')->get();
        
        $component_item_id      = [];

        //Arrange IDs for easy query
        foreach($items as $k => $item){
            
            if(!count($item->MaterialCanvass)){
                $items->forget($k);
                continue;
            }

            $component_item_id[]    = $item->component_item_id; 
            $material_quantity_id[] = $item->material_quantity_id;
            $material_item_id[]     = $item->material_item_id;
        }

        $suppliers              = Supplier::orderBy('name','ASC')->get();
        $component_items        = ComponentItem::whereIn('id',$component_item_id)->get();
        $material_quantities    = MaterialQuantity::whereIn('id',$material_quantity_id)->get();

        $material_items = DB::table('material_items')->whereIn('id',$material_item_id)->get();


        $component_item_arr = [];

        //Arrange component item by id
        foreach($component_items as $ci){
            $component_item_arr[$ci->id] = $ci;
        }

        $material_item_arr = [];

        //Arrange material item by id
        foreach($material_items as $mi){
            $material_item_arr[$mi->id] = $mi;
        }

        $payment_terms = PaymentTerm::toOptions();

        return view('review/material_canvass/display',[
            'material_quantity_request' => $materialQuantityRequest,
            'project'                   => $project,
            'section'                   => $section,
            'component'                 => $component,
            'items'                     => $items,
            'component_item_arr'        => $component_item_arr,
            'material_item_arr'         => $material_item_arr,
            'suppliers'                 => $suppliers,
            'payment_terms'             => $payment_terms,
            'contract_item'             => $contract_item
        ]);
    }


    public function _approve(Request $request){

        $id = (int) $request->input('id');

        $materialCanvass = MaterialCanvass::find($id);

        if(!$materialCanvass){

            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }

        if($materialCanvass->status != 'PEND'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record cannot be approved, status is not pending',
                'data'      => []
            ]);
        }

        $user_id = Auth::user()->id;

        $materialCanvass->approved_by = $user_id;
        $materialCanvass->status      = 'APRV';
        $materialCanvass->approved_at = Carbon::now();
        
        $materialCanvass->save();
        
        return response()->json([
            'status' => 1,
            'message' => '',
            'data' => []
        ]);
    }

    public function _reject(Request $request){

        $id = (int) $request->input('id');

        $materialCanvass = MaterialCanvass::find($id);

        if(!$materialCanvass){

            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }

        if($materialCanvass->status != 'PEND'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record cannot be disapproved, status is not pending',
                'data'      => []
            ]);
        }

        $user_id = Auth::user()->id;

        $materialCanvass->disapproved_by = $user_id;
        $materialCanvass->status      = 'REJC';
        $materialCanvass->disapproved_at = Carbon::now();
        
        $materialCanvass->save();
        
        return response()->json([
            'status' => 1,
            'message' => '',
            'data' => []
        ]);
    }

  
  
}
