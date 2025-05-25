<?php

namespace App\Http\Controllers\Review;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Section;
use App\Models\Component;
use App\Models\ContractItem;
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
            'projects.id AS project_id',
            'projects.name AS project_name', 
            'sections.name AS section_name',
            'contract_items.id AS contract_item_id',
            DB::raw('CONCAT(contract_items.item_code," ",contract_items.description) AS contract_item')
        );
        
        if($query != ''){
            $component = $component->where('components.name','LIKE','%'.$query.'%');
        }

        if($project_id){
            $component = $component->where('project_id','=',$project_id);

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

    public function display($contract_item_id, $component_id = null){

        if(!$this->hasAccess('component:all:view')){
            return view('access_denied');
        }

        $contract_item_id = (int) $contract_item_id;
        $component_id     = (int) $component_id;

        $contract_item  = ContractItem::findOrFail($contract_item_id);

        if($component_id){

            $component  = Component::findOrFail($component_id);
            
            if($component->contract_item_id != $contract_item->id){
                return abort(404);
            }

        }else{
            
            $component = Component::where('contract_item_id',$contract_item_id)->first();

            if(!$component){
                return abort(404);
            }
        }


        $section          = $component->Section;
        $project          = $section->Project;
    
        $component_items  = $component->componentItems()->orderBy('id','ASC')->withCount('materialQuantities')->get();

        
        $materialItems   = [];
        
        foreach(MaterialItem::get() as $mi){
        
            $materialItems[ $mi->id ] = $mi;
        }
        
        //$hash = generateComponentHash($project,$section,$component,$componentItems,$materialItems);

        $unit_options  = Unit::toOptions();

        $contract_items = $section->ContractItems;

        $contract_item_arr  = [];
        $components_arr     = [];
    
        $contract_grand_total_amount    = 0;
        $ref_1_grand_total_amount       = 0;
        $budget_grand_total_amount      = 0;

        $ref_1_total_amount     = 0;
        $material_total_amount  = 0;
        
        foreach($contract_items as $con_item){
            
            
            $contract_grand_total_amount    = $contract_grand_total_amount + ($con_item->contract_quantity * $con_item->contract_unit_price);
            $ref_1_grand_total_amount       = $ref_1_grand_total_amount + ($con_item->ref_1_quantity * $con_item->ref_1_unit_price);
            
            $components = $con_item->Components;

            //Skip contract items with no maintained components
            if(!count($components)) continue;


            $contract_item_arr[$con_item->id] = [
                'data'              => $con_item,
                'total_quantity'    => 0,
                'total_amount'      => 0
            ];

    
            foreach($components as $comp){
    
                $component_items                    = $comp->ComponentItems;
                $component_items_total_quantity     = 0;
                $component_items_total_amount       = 0;

                foreach($component_items as $comp_item){
    
                     //Total the quantity for all component item
                    if($comp_item->sum_flag && $comp_item->function_type_id == 4){ //As Equivalent function type
                        
                        $component_items_total_quantity = $component_items_total_quantity + ($comp_item->quantity * $comp_item->function_variable * $comp->use_count);
                    
                    }else if($comp_item->sum_flag && ($comp_item->unit_id == $comp->unit_id)){
                       
                        $component_items_total_quantity = $component_items_total_quantity + $comp_item->quantity;
                    
                    }
                    
                    $component_items_total_amount = $component_items_total_amount + ($comp_item->quantity * $comp_item->budget_price);
                }//foreach
    
                $components_arr[$comp->id] = (object) [
                    'contract_item_id'  => $con_item->id,
                    'data'              => $comp,
                    'total_quantity'    => $component_items_total_quantity,
                    'total_amount'      => $component_items_total_amount
                ];
                    
                if($comp->unit_id == $con_item->unit_id && $comp->sum_flag == true){
                    $contract_item_arr[$con_item->id]['total_quantity'] = $contract_item_arr[$con_item->id]['total_quantity'] + $comp->quantity;
                } 
                
                //Contract item row level
                $contract_item_arr[$con_item->id]['total_amount'] = $contract_item_arr[$con_item->id]['total_amount'] + $component_items_total_amount;
                
                //Grand total level
                $budget_grand_total_amount = $budget_grand_total_amount + $component_items_total_amount;
            
            }//foreach

            $contract_item_arr[$con_item->id] = (object) $contract_item_arr[$con_item->id];

        }//foreach

       
        $contract_amount = $contract_item->contract_unit_price * $contract_item->contract_quantity;

        return view('review/component/display',[
            'project'               => $project,
            'section'               => $section,
            'contract_item'         => $contract_item,
            'component'             => $component,
            'component_items'       => $component->ComponentItems,
            'materialItems'         => $materialItems,
            'hash'                  => '',
            'unit_options'          => $unit_options,
            'contract_amount'       => $contract_amount,
            'contract_item_arr'     => $contract_item_arr,
            'component_arr'         => $components_arr,
            'contract_grand_total_amount'   => $contract_grand_total_amount,
            'ref_1_grand_total_amount'      => $ref_1_grand_total_amount,
            'budget_grand_total_amount'     => $budget_grand_total_amount,
        ]);
    }


    public function _approve(Request $request){

        if(!$this->hasAccess('component:all:approve')){
            return response()->json([
                'status'    => 0,
                'message'   => 'Access Denied',
                'data'      => []
            ]);
        }

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

        if($component->status != 'PEND' || $component->status == 'REJC'){
            return response()->json([
                'status' => 0,
                'message'=>'Cannot approve this record, it has status '.$component->status,
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

        if(!$this->hasAccess('component:all:reject')){
            return response()->json([
                'status'    => 0,
                'message'   => 'Access Denied',
                'data'      => []
            ]);
        }

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
                'message'=>'Record not found',
                'data'=> []
            ]);
        }

        if($component->status != 'PEND' || $component->status == 'APRV'){
            return response()->json([
                'status' => 0,
                'message'=>'Cannot reject this record, it has status '.$component->status,
                'data'=> []
            ]);
        }

        
        $user_id = Auth::user()->id;

        $component->status      = 'REJC';
        $component->rejected_by = $user_id;
        $component->rejected_at = Carbon::now();
        
        $component->save();

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> []
        ]);
    }

    public function _revert_to_pending(Request $request){

        if(!$this->hasAccess('component:all:revert_to_pending')){
            return response()->json([
                'status'    => 0,
                'message'   => 'Access Denied',
                'data'      => []
            ]);
        }

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
                'message'=>'Record not found',
                'data'=> []
            ]);
        }

        if($component->status == 'PEND'){
            return response()->json([
                'status' => 0,
                'message'=>'The status for this record is already pending',
                'data'=> []
            ]);
        }

        
        $user_id = Auth::user()->id;

        $component->status      = 'PEND';
        $component->updated_by  = $user_id;
        $component->updated_at  = Carbon::now();
        
        $component->save();

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> []
        ]);
    }

}