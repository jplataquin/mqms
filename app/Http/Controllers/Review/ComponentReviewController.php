<?php

namespace App\Http\Controllers\Review;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Section;
use App\Models\Component;
use Carbon\Carbon;
use App\Http\Controllers\Controller;


class ComponentReviewController extends Controller
{

    public function list(){

        return view('review/component/list');
    }

    public function _list(Request $request){

        //todo check role

        $page       = (int) $request->input('page')     ?? 1;
        $limit      = (int) $request->input('limit')    ?? 0;
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $query      = $request->input('query')          ?? '';
        $result     = [];

        $component = new Component();

        $component = $component->where('status','=','PEND');

        $component = $component->join('projects', 'project.id', '=', 'component.project_id')
        ->join('sections', 'section.id', '=', 'component.section_id')
        ->select('component.*', 'project.name AS project_name', 'section.name AS section_name')
        
        if($query != ''){
            $component = $component->where('name','LIKE','%'.$query.'%');
        }

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $component->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $component->orderBy($orderBy,$order)->get();
        }

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> $result
        ]);
    }

}