<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\MaterialQuantityRequest;
use App\Models\MaterialQuantityRequestItem;
use Carbon\Carbon;

class ObjectivesController extends Controller
{
    public function material(){
        $projects = Project::where('status','ACTV')->get();

        return view('objectives/material',[
            'projects' => $projects
        ]);
    }

    public function _material(Request $request){

        $project_id = (int) $request->input('project_id');
        $from       = $request->input('from');
        $to         = $request->input('to');


        $material_requests = MaterialQuantityRequest::get();

        // if($project_id){

        //     $project = Project::find($project_id);

        //     if(!$project){
        //         return response()->json([
        //             'status'    => 0,
        //             'message'   => 'Project not found',
        //             'data'      => []
        //         ]);
        //     }

        //     if($project->status != 'ACTV'){
        //         return response()->json([
        //             'status'    => 0,
        //             'message'   => 'Project not in status active',
        //             'data'      => []
        //         ]);
        //     }

        //     $material_requests = $material_requests->where('project_id',$project_id);
        // }
        
        if($from == '' && $to == ''){
            $from = Carbon::now();
          
            
            $to = Carbon::now();
            $to->addDays(5);
            
        }

        // $material_requests = $material_requests->where('date_needed','!=',null);

        // $material_requests = $material_requests->where('date_needed','>=',$from->format('Y-m-d'));
                
        // $material_requests = $material_requests->where('date_needed','<=',$to->format('Y-m-d'));

        // $material_requests = $material_requests->orderBy('date_needed','DESC');

        //$material_requests = $material_requests->get();

        $project_arr = [];

        $result = [];

        foreach($material_requests as $mr){

            if(!isset($project_arr[$mr->project_id])){

                $proj = Project::find($mr->project_id);

                $project_arr[$mr->project_id] = $proj;
            }

           

           if(!isset($result[$mr->project_id])){
                $result[$mr->project_id] = [];
           }
           
            if(!isset($result[$mr->project_id][$mr->id])){
                $result[$mr->project_id][$mr->id] = [];
           }

           if(!isset($result[$mr->project_id][$mr->id]['items'])){
                $result[$mr->project_id][$mr->id]['items'] = [];
           }

           $items = MaterialQuantityRequestItem::where('material_quantity_request_id',$mr->id)->with('material_item')->get();
           
           foreach($items as $item){
                
                $material_item = MaterialItem::find($item->material_item_id);

                if($material_item){
                    $result[$mr->project_id][$mr->id]['items'][] = $material_item->formatedName + 'x' + number_format($item->quantity,2); 
                }
           }


           $result[$mr->project_id][$mr->id]['material_request'] = $mr;
        
        }


        return response()->json([
            'status' => 1,
            'message' => '',
            'data' => [
                'result'        => $result,
                'project_arr'   => $project_arr,
                'from'          => $from->format('Y-m-d'),
                'to'            => $to->format('Y-m-d'),
                'material_requests' => $material_requests
            ]
        ]);
    }
}
