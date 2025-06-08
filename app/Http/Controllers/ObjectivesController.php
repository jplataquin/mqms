<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\MaterialQuantityRequest;
use App\Models\MaterialQuantityRequestItem;
use App\Models\MaterialItem;
use Carbon\Carbon;
use DateTime;

class ObjectivesController extends Controller
{
    public function material(){

        $projects = Project::where('status','ACTV')->orderBy('name','ASC')->get();

        $from   = Carbon::now();
        $to     = Carbon::now();
        $to->addDays(5);
        
        return view('objectives/material',[
            'projects'  => $projects,
            'from'      => $from->format('M d, Y'),
            'to'        => $to->format('M d, Y')
        ]);
    }

    public function _material(Request $request){

        $project_id = (int) $request->input('project_id');
        $from       = $request->input('from');
        $to         = $request->input('to');


        $material_requests = MaterialQuantityRequest::where('status','APRV');

        if($project_id){

            $project = Project::find($project_id);

            if(!$project){
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Project not found',
                    'data'      => []
                ]);
            }

            if($project->status != 'ACTV'){
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Project not in status active',
                    'data'      => []
                ]);
            }

            $material_requests = $material_requests->where('project_id',$project_id);
        }
        
        if($from){
            $from = DateTime::createFromFormat('M d, Y', $from);
        
        }else{
            
            return response()->json([
                'status'    => 0,
                'message'   => 'From date is required',
                'data'      => []
            ]);
        }

        if($to){
        
            $to = DateTime::createFromFormat('M d, Y', $to);
        
        }else{

            return response()->json([
                'status'    => 0,
                'message'   => 'To date is required',
                'data'      => []
            ]);
        }
        
        $material_requests = $material_requests->where('date_needed','!=',null);

        if($from){
            $material_requests = $material_requests->where('date_needed','>=',$from->format('Y-m-d'));
        }

        if($to){
            $material_requests = $material_requests->where('date_needed','<=',$to->format('Y-m-d'));
        }

        $material_requests = $material_requests->orderBy('date_needed','DESC');

        $material_requests = $material_requests->get();

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
            
           
           if(!isset($result[$mr->project_id][$mr->date_needed])){
                $result[$mr->project_id][$mr->date_needed] = [];
           }

           if(!isset($result[$mr->project_id][$mr->date_needed][$mr->id])){
                $result[$mr->project_id][$mr->date_needed][$mr->id] = [];
           }


           if(!isset($result[$mr->project_id][$mr->date_needed][$mr->id]['items'])){
                $result[$mr->project_id][$mr->date_needed][$mr->id]['items'] = [];
           }

           $items = MaterialQuantityRequestItem::where('material_quantity_request_id',$mr->id)->get();
           
           foreach($items as $item){
                
                $material_item = MaterialItem::find($item->material_item_id);

                if($material_item){
                    $result[$mr->project_id][$mr->date_needed][$mr->id]['items'][] = $material_item->formatted_name. ' x ' .number_format($item->requested_quantity,2); 
                }
           }

            $result[$mr->project_id][$mr->date_needed][$mr->id]['material_request'] = $mr;
        }


        return response()->json([
            'status' => 1,
            'message' => '',
            'data' => [
                'result'        => $result,
                'project_arr'   => $project_arr,
                'from'          => $from->format('Y-m-d'),
                'to'            => $to->format('Y-m-d')
            ]
        ]);
    }
}
