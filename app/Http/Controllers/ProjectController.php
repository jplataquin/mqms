<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;


class ProjectController extends Controller
{
    public function create(){

        return view('project/create');
    }

    public function display($id){

        $id = (int) $id;

        $project = Project::findOrFail($id);

        return view('project/display',[
            'project' => $project
        ]);
    }


    public function list(){

        return view('project/list');
    }


    public function _create(Request $request){

        //todo check role

        $name   = $request->input('name') ?? '';
        $status = $request->input('status');

        $validator = Validator::make($request->all(),[
            'name' => [
                'required',
                'max:255',
                'unique:projects'
            ],
            'status' => [
                'required',
                'max:4'
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

        $project = new Project();

        $project->name          = $name;
        $project->status        = $status;
        $project->created_by    = $user_id;
    

        $project->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id'=> $project->id
            ]
        ]);

    }

    public function _update(Request $request){

        //todo check role

        $id       = (int) $request->input('id') ?? 0;
        $name     = $request->input('name') ?? '';
        $status   = $request->input('status') ?? '';
        
        $validator = Validator::make($request->all(),[
            'id'   => [
                'required',
                'integer',               
            ],
            'name' => [
                'required',
                'max:255',
                Rule::unique('projects')->ignore($id),
            ],
            'status' => [
                'required',
                'max:4'
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
        $project = Project::find($id);

        if(!$project){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => [
                    'id' => $id
                ]
            ]);
        }

        $project->name                         = $name;
        $project->status                       = $status;
        $project->updated_by                   = $user_id;

        $project->save();


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


        $page       = (int) $request->input('page')     ?? 1;
        $limit      = (int) $request->input('limit')    ?? 10;
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $query      = $request->input('query')          ?? '';
        $result = [];

        $project = new Project();

        if($query != ''){
            $project = $project->where('name','LIKE','%'.$query.'%');
        }

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $project->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $project->orderBy($orderBy,$order)->take($limit)->get();
        }

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> $result
        ]);
    }

    public function _delete(Request $request){

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

        $project = Project::find($id);

        if(!$project){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }
        
       
        if(!$project->delete()){
           
           return response()->json([
               'status'    => 0,
               'message'   => '' ,
               'data'      => []
           ]);
        }

        return response()->json([
           'status'    => 1,
           'message'   => '',
           'data'      => []
       ]);
    }

    public function _request_void(Request $request){
       
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

        $project = Project::find($id);

        if(!$project){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }
        
       
        $project->status = 'REVO';
        $project->save();

        return response()->json([
           'status'    => 1,
           'message'   => '',
           'data'      => []
       ]);
    }
}
