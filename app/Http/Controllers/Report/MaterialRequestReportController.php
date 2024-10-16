<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Project;
use App\Models\Section;
use App\Models\Component;
use App\Models\Supplier;
use App\Models\User;
use App\Models\MaterialQuantityRequestItem;
use App\Models\MaterialQuantityRequest;
use App\Models\PurchaseOrderItem;
use App\Models\MaterialItem;
use App\Models\MaterialGroup;
use Illuminate\Support\Facades\DB;

class MaterialRequestReportController extends Controller
{
    public function parameters(){

        $projects = Project::where('deleted_at',null)->get();

        $material_groups = MaterialGroup::where('deleted_at',null)->get();

        $users = User::where('deleted_at',null)->get();

        return view('report/material_request/parameters',[
            'projects'          => $projects,
            'material_groups'   => $material_groups,
            'users'             => $users
        ]);
    }

    public function generate(Request $request){

        $project_id             = $request->input('project_id');
        $section_id             = $request->input('section_id');
        $contract_item_id       = $request->input('contract_item_id');
        $component_id           = $request->input('component_id');
        $from                   = $request->input('from');
        $to                     = $request->input('to');
        $requested_by           = $request->input('requested_by');
        $status                 = $request->input('status');
        $material_item_id_arr   = explode(',',$request->input('material_items'));

        $project_name       = '';
        $section_name       = '';
        $contract_item_name = '';
        $component_name     = '';
        $contract_item      = null;

        $validator = Validator::make($request->all(),[
            'project_id' =>[
                'required',
                'integer',
                'gte:1'
            ],
            'section_id' =>[
                'required',
                'integer',
                'gte:1'
            ],
            'contract_item_id' =>[
                'required',
                'integer',
                'gte:1'
            ],
            'material_group_id'   => [
                'required',
                'integer',
                'gte:1'               
            ],
            'from' => [
                'date_format:Y-m-d'
            ],
            'to' => [
                'date_format:Y-m-d'
            ]
        ]);

        if ($validator->fails()) {
            
            return view('/report/material_request/error',[
                'message'          => '',
                'validation_error' => $validator->messages()
            ]);
        }

        //Query material request
        $material_request = new MaterialQuantityRequest();

        if($project_id){

            $project_id         = (int) $project_id;
            $material_request   = $material_request->where('project_id',$project_id);

            
            $project        = Project::find($project_id);
            
            if($project){
                return view('/report/material_request/error',[
                    'message'          => 'Project record not found',
                    'validation_error' => []
                ]);
            }

            $project_name   = $project->name;

            if($section_id){

                $section_id         = (int) $section_id;
                $material_request   = $material_request->where('section_id',$section_id);

                $section        = Section::find($section_id);
                
                if($section){
                    return view('/report/material_request/error',[
                        'message'          => 'Section record not found',
                        'validation_error' => []
                    ]);
                }

                $section_name = $section->name;

                if($contract_item_id){
                   
                    $contract_item_id = (int) $contract_item_id;
                    $material_request = $material_request->where('contract_item_id',$contract_item_id);
                    
                    $contract_item        = ContractItem::find($contract_item_id);
                    
                    if($contract_item){
                        return view('/report/material_request/error',[
                            'message'          => 'Contract Item record not found',
                            'validation_error' => []
                        ]);
                    }

                    $contract_item_name  = $contract_item->name;

                    if($component_id){
                        $component_id       = (int) $component_id;
                        $material_request   = $material_request->where('component_id',$component_id);
                        
                    }            
                }   
            }
        }/*****/

        if($requested_by){
            $material_request = $material_request->where('created_by',$requested_by);
        }

        if($from){
            $material_request = $material_request->where('created_at','>=',$from.' 00:00:00');
        }

        if($to){
            $material_request = $material_request->where('created_at','<=',$to.' 11:59:59');
        }

        if($status){
            $material_request = $material_request->where('status',$status);
        }


        $material_request_results = $material_request->get();
        $material_request_id_arr  = [];

        foreach($material_request_results as $row){
            $material_request_id_arr[] = $row->id;
        }

        //Get all material request ids and arrange them into budget model
        $budget_model = $this->get_arranged_budget_model(
            $contract_item,
            $material_requst_id_arr
        );

        $material_request_items = new MaterialQuantityRequestItem();

        if($material_request_id_arr){
            $material_request_items = $material_request_items->whereIn('material_quantity_request_id',$material_request_id_arr);
        }
        

        if($material_item_id_arr){
           $material_group = MaterialGroup::findOrFail($material_group_id);

           $material_items = $material_group->Items()->where('deleted_at',null)->get();

           foreach($material_items as $row){
                $material_item_id_arr[] = $row->id;
           }
        }  
        
        if(!$material_item_id_arr){
            return view('/report/material_request/error',[
                'message'          => 'No material items selected',
                'validation_error' => []
            ]); 
        }

        $material_request_items = $material_request_items->whereIn('material_item_id',$material_item_id_arr);
        
        $result = [];

        return view('/report/material_request/generate',[
            'project_name'          => $project_name,
            'section_name'          => $section_name,
            'contract_item_name'    => $contract_item_name,
            'component_name'        => $component_name,
            'from'                  => $from,
            'to'                    => $to,
            'reesult'               => $result
        ]);
    }


    public function get_arranged_budget_model($contract_item,$material_request_id_arr){
        
        $material_request_items = $this->get_arranged_material_request_items($material_request_id_arr);

        $budget = [];

        $components = $contract_item->Components;

        foreach($components as $component){

            if($component->deleted_at != null) continue;

            if(!isset($budget[$component->id])){
                $budget[$component->id] = [];
            }

            $component_items = $component->ComponentItems;

            foreach($component_items as $component_item){

                if($component_item->deleted_at != null) continue;

                if( !isset($budget[$component->id][$component_item->id]) ){
                    $budget[$component->id][$component_item->id] = [];
                }

                $material_quantities = $component_item->MaterialQuantities;

                foreach($material_quantities as $material_quantity){

                    $budget[$component->id][$component_item->id][] = $material_quantity;
                    


                }//foreach

            }//foreach

        }//foreach
    }


    public get_arranged_material_request_items($material_request_id_arr){

        $material_request_items = MaterialRequestItem::whereIn('material_request_id', $material_request_id_arr)->get();

        $material_request_items_arr = [];

        foreach($material_request_items as $mri){
            $material_request_items_arr[$mri->id] = $mri;
        }
    }
}