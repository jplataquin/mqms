<?php

namespace App\Http\Controllers\Review;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Section;
use App\Models\Component;
use App\Models\MaterialQuantityRequest;
use App\Models\MaterialQuantityRequestItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class MaterialQuantityRequestReviewController extends Controller
{
    public function create(){

        return view('access_code/create');
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
                'value'     => $componentItem->id,
                'text'      => $componentItem->name,
                'unit'      => $componentItem->unit,
                'quantity'  => $componentItem->quantity
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

        return view('review/material_quantity_request/display',[
            'project'                   => $project,
            'section'                   => $section,
            'component'                 => $component,
            'material_quantity_request' => $materialQuantityRequest,
            'request_items'             => $request_items,
            'material_options'          => $material_options,
            'componentItem_options'     => $componentItem_options
        ]);
    }

    public function list(){

        $projects = Project::orderBy('name','ASC')->where('status','=','ACTV')->get();

        return view('review/material_quantity_request/list',[
            'projects' => $projects
        ]);
    }

 
 
    public function _list(Request $request){

        //todo check role

        $page       = (int) $request->input('page')     ?? 1;
        $limit      = (int) $request->input('limit')    ?? 10;
        $project_id = (int) $request->input('project_id')  ?? 0;
        $section_id      = (int) $request->input('section_id')  ?? 0;
        $component_id    = (int) $request->input('component_id')  ?? 0;
        $query      = (int) $request->input('query')    ?? 0;
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $result = [];

        $materialQuantityRequest = new MaterialQuantityRequest();

        $materialQuantityRequest = $materialQuantityRequest->where('status','=','PEND');
        
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
    
    public function _approve(Request $request){
        $id = (int) $request->input('id');


        $validator = Validator::make($request->all(),[
            'id' => [
                'required',
                'integer'
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $materialQuantityRequest = MaterialQuantityRequest::find($id);

        if(!$materialQuantityRequest){
            return response()->json([
                'status'    => 0,
                'message'   => 'No record found',
                'data'      => []
            ]);
        }

        if($materialQuantityRequest->status != 'PEND'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record is no longer in Pending status',
                'data'      => []
            ]);
        }

        $user_id = Auth::user()->id;

        DB::beginTransaction();

        try {

            $mytime = Carbon::now();

            $materialQuantityRequest->status        = 'APRV';
            $materialQuantityRequest->approved_by   = $user_id;
            $materialQuantityRequest->approved_at   = $mytime->toDateTimeString();
            $materialQuantityRequest->save();

            $affected = DB::table('material_quantity_request_items')
                ->where('material_quantity_request_id', $id)
                ->update(['status' => 'APRV']);

            DB::commit();

            return response()->json([
                'status'    => 1,
                'message'   => '',
                'data'      => []
            ]);

        }catch(\Exception $e){

            return response()->json([
                'status'    => 0,
                'message'   => 'Record update failed',
                'data'      => $e
            ]);

            DB::rollback();

            return false;
            
         }
    }


    public function _disapprove(Request $request){
        $id = (int) $request->input('id');


        $validator = Validator::make($request->all(),[
            'id' => [
                'required',
                'integer'
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $materialQuantityRequest = MaterialQuantityRequest::find($id);

        if(!$materialQuantityRequest){
            return response()->json([
                'status'    => 0,
                'message'   => 'No record found',
                'data'      => []
            ]);
        }

        if($materialQuantityRequest->status != 'PEND'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record is no longer in Pending status',
                'data'      => []
            ]);
        }

        $user_id = Auth::user()->id;

        DB::beginTransaction();

        try {

            $mytime = Carbon::now();

            $materialQuantityRequest->status           = 'DPRV';
            $materialQuantityRequest->disapproved_by   = $user_id;
            $materialQuantityRequest->disapproved_at   = $mytime->toDateTimeString();
            $materialQuantityRequest->save();

            $affected = DB::table('material_quantity_request_items')
                ->where('material_quantity_request_id', $id)
                ->update(['status' => 'DPRV']);

            DB::commit();

            return response()->json([
                'status'    => 1,
                'message'   => '',
                'data'      => []
            ]);

        }catch(\Exception $e){

            return response()->json([
                'status'    => 0,
                'message'   => 'Record update failed',
                'data'      => $e
            ]);

            DB::rollback();

            return false;
            
         }
    }
} 