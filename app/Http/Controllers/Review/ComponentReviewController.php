<?php

namespace App\Http\Controllers\Review;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Section;
use App\Models\Component;
use App\Models\MaterialItem;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Carbon\Carbon;


class ComponentReviewController extends Controller
{

    public function list(){

        $projects = Project::orderBy('name','ASC')->where('status','=','ACTV')->get();

        
        return view('review/component/list',[
            'projects' => $projects
        ]);
    }

    public function _list(Request $request){

        //todo check role

        $page               = (int) $request->input('page')                     ?? 1;
        $limit              = (int) $request->input('limit')                    ?? 0;
        $orderBy            = $request->input('order_by')                       ?? 'id';
        $order              = $request->input('order')                          ?? 'DESC';
        $query              = $request->input('query')                          ?? '';
        $project_id         = (int) $request->input('project_id')               ?? 0;
        $section_id         = (int) $request->input('section_id')               ?? 0;
        $contract_item_id   = (int) $request->input('contract_item_id')         ?? 0;
        $result             = [];

        $component = new Component();

        $component = $component->where('components.status','=','PEND');

        $component = $component
        ->join('sections', 'sections.id', '=', 'components.section_id')
        ->join('projects', 'projects.id', '=', 'sections.project_id')
        ->join('contract_items','contract_items.id','=','components.contract_item_id')
        ->select(
            'components.*', 
            'projects.name AS project_name', 
            'sections.name AS section_name',
            DB::raw('CONCAT(contract_items.item_code," ",contract_items.description) AS contract_item')
        );
        
        if($query != ''){
            $component = $component->where('components.name','LIKE','%'.$query.'%');
        }

        if($project_id){
            $component = $component->where('components.project_id','=',$project_id);

            if($section_id){
                $component = $component->where('components.section_id','=',$section_id);

                if($contract_item_id){
                    $component = $component->where('components.contract_item_id','=',$contract_item_id);
                }
            }
        }

        

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $component->orderBy('components.'.$orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $component->orderBy('components.'.$orderBy,$order)->get();
        }

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> $result
        ]);
    }

    public function display($id){

        $component = Component::findOrFail($id);

        $section         = $component->section;
        $project         = $section->project;
        $componentItems  = $component->componentItems()->orderBy('id','ASC')->withCount('materialQuantities')->get();

        
        $materialItems   = [];
        
        foreach(MaterialItem::get() as $mi){
        
            $materialItems[ $mi->id ] = $mi;
        }
        
        $hash = generateComponentHash($project,$section,$component,$componentItems,$materialItems);

        $unit_options  = Unit::toOptions();


        return view('review/component/display',[
            'project'           => $project,
            'section'           => $section,
            'component'         => $component,
            'componentItems'    => $componentItems,
            'materialItems'     => $materialItems,
            'hash'              => $hash,
            'unit_options'      => $unit_options
        ]);
    }


    public function _approve(Request $request){

        //todo check role

        $id = (int) $request->input('id') ?? 0;

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

        $component = Component::find($id);

        if(!$component){
            return response()->json([
                'status' => 0,
                'message'=>'Error: Record not found',
                'data'=> []
            ]);
        }

        if($component->status != 'PEND'){
            return response()->json([
                'status' => 0,
                'message'=>'Error: Status for this record is no longer pending',
                'data'=> []
            ]);
        }

        
        $user_id = Auth::user()->id;

        $component->status      = 'APRV';
        $component->approved_by = $user_id;
        $component->approved_at = Carbon::now();
        
        $component->save();

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> []
        ]);
        
    }

    public function _reject(Request $request){

        //todo check role

        $id = (int) $request->input('id') ?? 0;

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

        
        $component = Component::find($id);

        if(!$component){
            return response()->json([
                'status' => 0,
                'message'=>'Error: Record not found',
                'data'=> []
            ]);
        }

        if($component->status != 'PEND'){
            return response()->json([
                'status' => 0,
                'message'=>'Error: Status for this record is no longer pending',
                'data'=> []
            ]);
        }

        
        $user_id = Auth::user()->id;

        $component->status      = 'REJC';
        $component->approved_by = $user_id;
        $component->approved_at = Carbon::now();
        
        $component->save();

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> []
        ]);
    }

}