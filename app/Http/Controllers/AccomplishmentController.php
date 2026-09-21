<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Section;
use App\Models\ContractItem;
use App\Models\Component;
use App\Models\Accomplishment;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AccomplishmentController extends Controller
{

    public function list(){

        return view('accomplishment/list');
    }

    public function _list(Request $request){

      
        $page       = (int) ($request->input('page') ?? 1);
        $limit      = (int) ($request->input('limit') ?? 10);
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

        return view('accomplishment/section_list',[
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
        
        $section        = Section::findOrFail($id);
        $project        = $section->Project;

        return view('accomplishment/contract_item_list',[
            'section' => $section,
            'project' => $project
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

        $section       = $contract_item->Section;
        $project       = $section->Project;

        return view('accomplishment/component_list',[
            'contract_item' => $contract_item,
            'section'       => $section,
            'project'       => $project
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
        
        $component     = Component::findOrFail($id);
        $contract_item = $component->ContractItem;
        $section       = $contract_item->Section;
        $project       = $section->Project;

        $accomplishments = $component->Accomplishments()->orderBy('entry_data', 'desc')->get();

        return view('accomplishment/component_display',[
            'project'               => $project,
            'section'               => $section,
            'contract_item'         => $contract_item,
            'component'             => $component,
            'accomplishments'       => $accomplishments
        ]);
    }

    public function create($component_id){

        $component     = Component::findOrFail($component_id);
        $contract_item = $component->ContractItem;
        $section       = $contract_item->Section;
        $project       = $section->Project;

        return view('accomplishment/create',[
            'project'               => $project,
            'section'               => $section,
            'contract_item'         => $contract_item,
            'component'             => $component
        ]);
    }

    public function _create(Request $request){

        $validator = Validator::make($request->all(), [
            'component_id' => 'required|integer|exists:components,id',
            'type'         => 'required|in:ACTUAL,TARGET',
            'entry_date'   => 'required|date',
            'quantity'     => 'required|numeric',
            'remarks'      => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $user_id = Auth::user()->id;

        $accomplishment = new Accomplishment();
        $accomplishment->component_id   = $request->input('component_id');
        $accomplishment->type           = $request->input('type');
        $accomplishment->entry_data     = $request->input('entry_date');
        $accomplishment->quantity       = $request->input('quantity');
        $accomplishment->remarks        = $request->input('remarks');
        $accomplishment->created_by     = $user_id;

        $accomplishment->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => $accomplishment
        ]);
    }

    public function add($component_id){

        $component     = Component::findOrFail($component_id);
        $contract_item = $component->ContractItem;
        $section       = $contract_item->Section;
        $project       = $section->Project;

        return view('accomplishment/add',[
            'project'               => $project,
            'section'               => $section,
            'contract_item'         => $contract_item,
            'component'             => $component
        ]);
    }

    public function _add(Request $request){

        if(!$this->hasAccess(['accomplishment:all:create'])){
            return response()->json([
                'status'    => 0,
                'message'   => 'Access Denied',
                'data'      => []
            ]);
        }

        $validator = Validator::make($request->all(), [
            'component_id' => 'required|integer|exists:components,id',
            'entry_data'   => 'required|date',
            'quantity'     => 'required|numeric',
            'remarks'      => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $user_id = Auth::user()->id;

        $accomplishment = new Accomplishment();
        $accomplishment->component_id   = $request->input('component_id');
        $accomplishment->type           = 'ACTUAL'; // default enum required field
        $accomplishment->entry_data     = $request->input('entry_data');
        $accomplishment->quantity       = $request->input('quantity');
        $accomplishment->remarks        = $request->input('remarks');
        $accomplishment->created_by     = $user_id;

        $accomplishment->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => $accomplishment
        ]);
    }

    public function _record_list(Request $request){

        $component_id = (int) $request->input('component_id') ?? 0;
        $page         = (int) ($request->input('page') ?? 1);
        $limit        = (int) ($request->input('limit') ?? 10);
        $orderBy      = $request->input('order_by')       ?? 'entry_data';
        $order        = $request->input('order')          ?? 'DESC';
        $result       = [];

        $query = Accomplishment::with('CreatedBy')->where('component_id', $component_id);

        if($limit > 0){
            $offset = ($page-1) * $limit;
            $result = $query->orderBy($orderBy, $order)->skip($offset)->take($limit)->get();
        }else{
            $result = $query->orderBy($orderBy, $order)->get();
        }

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => $result
        ]);
    }

    public function display_record($id){

        $accomplishment = Accomplishment::findOrFail($id);
        $component      = $accomplishment->Component;
        $contract_item  = $component->ContractItem;
        $section        = $contract_item->Section;
        $project        = $section->Project;

        return view('accomplishment/display',[
            'project'               => $project,
            'section'               => $section,
            'contract_item'         => $contract_item,
            'component'             => $component,
            'accomplishment'        => $accomplishment
        ]);
    }
}
