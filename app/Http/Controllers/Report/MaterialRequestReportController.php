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
use App\Models\ComponentUnit;
use App\Models\MaterialGroup;
use Illuminate\Support\Facades\DB;

class MaterialRequestReportController extends Controller
{
    public function parameters(){

        $projects = Project::where('deleted_at',null)->get();

        $material_groups = MaterialGroup::where('deleted_at',null)->get();

        $users = User::where('deleted_at',null)->get();

        return view('report/material_quantity/parameters',[
            'projects'          => $projects,
            'material_groups'   => $material_groups,
            'users'             => $users
        ]);
    }

    public function generate(Request $request){

        $project_id         = $request->input('project_id');
        $section_id         = $request->input('section_id');
        $contract_item_id   = $request->input('contract_item_id');
        $component_id       = $request->input('component_id');
        $from               = $request->input('from');
        $to                 = $request->input('to');
        $requested_by       = $request->input('requested_by');
        $status             = $request->input('status');

        //Query material request
        $material_request = new MaterialQuantityRequest();

        if($project_id){

            $project_id = (int) $project_id;
            $material_request = $material_request->where('project_id',$project_id);

            if($section_id){
                $section_id = (int) $section_id;
                $material_request = $material_request->where('section_id',$section_id);

                if($contract_item_id){
                    $contract_item_id = (int) $contract_item_id;
                    $material_request = $material_request->where('contract_item_id',$contract_item_id);
                    
                    if($component_id){
                        $component_id = (int) $component_id;
                        $material_request = $material_request->where('component_id',$component_id);
                        
                    }            
                }   
            }
        }/*****/



        $material_item_id_arr = explode(',',$request->input('material_items'));

    }
}