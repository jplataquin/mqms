<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Section;
use App\Models\ComponentUnit;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;


class SectionController extends Controller
{
    public function create($project_id){

        $project_id = (int) $project_id;

        $project = Project::findOrFail($project_id);

        return view('section/create',[
            'project' => $project
        ]);
    }

    public function display($id){

        $id = (int) $id;

        $section = Section::findOrFail($id);

        $project = $section->project;

        $components = $section->components()->orderBy('id','ASC')->get();
        
        
        $unit_options = ComponentUnit::toOptions();


        return view('section/display',[
            'section'          => $section,
            'project'          => $project,
            'components'       => $components,
            'unit_options'     => $unit_options
        ]);
    }


    public function list(){

        return view('project/list');
    }

    public function print($id){
        
        $section = Section::findOrFail($id);

        $components = $section->Components;

        $unit_options = ComponentUnit::toOptions();
        echo 'asdsad';
        return view('section/print',[
            'section'          => $section,
            'components'       => $components,
            'unit_options'     => $unit_options
        ]);
    }


    public function _create(Request $request){

        //todo check role

        $name           = $request->input('name') ?? '';
        $status         = $request->input('status');
        $project_id     = (int) $request->input('project_id') ?? 0;

        //TODO check if project exists;

        $validator = Validator::make($request->all(),[
            'name' => [
                'required',
                'max:255',
                Rule::unique('sections')->where(
                function ($query) use ($project_id,$name) {
                    return $query
                    ->where('project_id', $project_id)
                    ->where('name', $name);
                }),
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        

        $user_id = Auth::user()->id;

        $section = new Section();

        $section->project_id    = $project_id;
        $section->name          = $name;
        $section->created_by    = $user_id;

        $section->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id'=> $section->id
            ]
        ]);

    }

    public function _update(Request $request){

        //todo check role

        $id         = (int) $request->input('id') ?? 0;
        $name       = $request->input('name') ?? '';
        
        $validator = Validator::make($request->all(),[
            'id'   => [
                'required',
                'integer',               
            ],
            'name' => [
                'required',
                'max:255',
                Rule::unique('sections')->where(
                function ($query) use ($id,$name) {
                    return $query
                    ->where('name', $name)
                    ->where('id','!=',$id);
                }),
            ]
        ]);

        if ($validator->fails()) {
            
            return response()->json([
                'status'    => 0,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $user_id = Auth::user()->id;
        $section = Section::find($id);

        if(!$section){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => [
                    'id' => $id
                ]
            ]);
        }

        $section->name                         = $name;
        $section->updated_by                  = $user_id;

        $section->save();


        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id' => $id
            ]
        ]);

    }

    public function _list(Request $request){

        //todo check role

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

    public function _delete(Request $request){

         //Check role
         $id = (int) $request->input('id');


         $validator = Validator::make($request->all(),[
             'id' => [
                 'required',
                 'integer',
             ]
         ]);
 
         if($validator->fails()){
             
             return response()->json([
                 'status'    => 0,
                 'message'   => 'Failed Validation',
                 'data'      => $validator->messages()
             ]);
         }
 
         $section = Section::find($id);
 
         if(!$section){
             return response()->json([
                 'status'    => 0,
                 'message'   => 'Record not found',
                 'data'      => []
             ]);
         }
         
         if(!$section->delete()){
            return response()->json([
                'status'    => 0,
                'message'   => 'Delete operation failed',
                'data'      => $e
            ]);
         }

         return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
        
    }
}
