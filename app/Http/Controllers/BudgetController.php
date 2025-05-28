<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Section;
use App\Models\ContractItem;
use App\Models\Component;
use App\Models\ComponentItem;

class BudgetController extends Controller
{

    public function list(){

        return view('budget/list');
    }

    public function _list(Request $request){

      
        $page       = (int) $request->input('page')     ?? 1;
        $limit      = (int) $request->input('limit')    ?? 10;
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $query      = $request->input('query')          ?? '';
        $status     = $request->input('status')         ?? '';
        $result = [];

        $project = new Project();

        $project = $project->where('deleted_at',null);
        $project = $project->where('status','ACTV');

        if($query != ''){
            $project = $project->where('name','LIKE','%'.$query.'%');
        }

        if($status != ''){
            $project = $project->where('status','=',$status);
        }

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $project->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $project->orderBy($orderBy,$order)->take($limit)->get();
        }

        return response()->json([
            'status'    => 1,
            'message'   =>'',
            'data'      => $result
        ]);
    }


    public function section_list($id){

        $project = Project::findOrFail($id);

        return view('budget/section_list',[
            'project' => $project
        ]);
    }

    public function _section_list(Request $request){


        $project_id = (int) $request->input('project_id') ?? 0;
        $page       = (int) $request->input('page')     ?? 1;
        $limit      = (int) $request->input('limit')    ?? 0;
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $query      = $request->input('query')          ?? '';
        $result     = [];

        $section = new Section();

        $section = $section->where('project_id',$project_id);

        if($query != ''){
            $section = $section->where('name','LIKE','%'.$query.'%');
        }

        //Filter deleted
        $section = $section->where('deleted_at','=',null);
        
        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $section->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $section->orderBy($orderBy,$order)->get();
        }

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> $result
        ]);
    }

    public function contract_item_list($id){
        
        $section = Section::findOrFail($id);

        return view('budget/contract_item_list',[
            'section' => $section
        ]);
    }

    public function _contract_item_list(Request $request){

        $section_id = (int) $request->input('section_id') ?? 0;
        $page       = (int) $request->input('page')     ?? 1;
        $limit      = (int) $request->input('limit')    ?? 0;
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $query      = $request->input('query')          ?? '';
        $result     = [];

        $contract_item = new ContractItem();

        if($section_id){
            $contract_item = $contract_item->where('section_id',$section_id);
        }

        if($query != ''){
            $contract_item = $contract_item->where(function($condition) use ($query){
                $condition->where('item_code','LIKE','%'.$query.'%')
                ->orWhere('description','LIKE','%'.$query.'%');
            });
        }

        //Filter out deleted records
        $contract_item = $contract_item->where('deleted_at','=',null);

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $contract_item->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $contract_item->orderBy($orderBy,$order)->get();
        }

        return response()->json([
            'status'    => 1,
            'message'   =>'',
            'data'      => $result
        ]);
    }

    public function component_list($id){
        
        $contract_item = ContractItem::findOrFail($id);

        return view('budget/component_list',[
            'contract_item' => $contract_item
        ]);
    }


    public function _component_list(Request $request){

        $contract_item_id   = (int) $request->input('contract_item_id') ?? 0;
        $page               = (int) $request->input('page')     ?? 1;
        $limit              = (int) $request->input('limit')    ?? 0;
        $orderBy            = $request->input('order_by')       ?? 'id';
        $order              = $request->input('order')          ?? 'DESC';
        $query              = $request->input('query')          ?? '';
        $result             = [];

        $component = new Component();

        if($contract_item_id){
            $component = $component->where('contract_item_id',$contract_item_id);
        }

        if($query != ''){
            $component = $component->where('name','LIKE','%'.$query.'%');
        }

        //Filter out deleted records
        $component = $component->where('deleted_at','=',null);

        //Filter out approved component
        $component = $component->where('status','APRV');

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $component->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $component->orderBy($orderBy,$order)->get();
        }

        return response()->json([
            'status'    => 1,
            'message'   =>'',
            'data'      => $result
        ]);
    }


    public function component_display($id){
        
        $component = Component::findOrFail($id);

        $component_items = ComponentItem::where('component_id',$id)
        ->where('deleted_at',null)
        ->get();

        $component_item_arr = [];

        foreach($component_items as $component_item){
            $component_item_arr[] = (object) [
                'data' => $component_item
            ];
        }

        return view('budget/component_display',[
            'component'             => $component,
            'component_item_arr'    => $component_item_arr 
        ]);
    }
}