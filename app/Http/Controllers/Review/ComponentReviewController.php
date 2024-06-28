<?php

namespace App\Http\Controllers\Review;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Section;
use App\Models\Component;
use App\Models\MaterialItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Carbon\Carbon;


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

        $component = $component->where('components.status','=','PEND');

        $component = $component
        ->join('sections', 'sections.id', '=', 'components.section_id')
        ->join('projects', 'projects.id', '=', 'sections.project_id')
        ->select('components.*', 'projects.name AS project_name', 'sections.name AS section_name');
        
        if($query != ''){
            $component = $component->where('components.name','LIKE','%'.$query.'%');
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

        return view('component/display',[
            'project'           => $project,
            'section'           => $section,
            'component'         => $component,
            'componentItems'    => $componentItems,
            'materialItems'     => $materialItems,
            'hash'              => $hash
        ]);
    }

}