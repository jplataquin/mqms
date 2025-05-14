<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Unit;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;


class ProjectController extends Controller
{
    public function create(){

        return view('project/create');
    }

    public function display($id,Request $request){

        $id = (int) $id;

        $project = Project::findOrFail($id);

        $user = auth()->user();

        if(!$this->hasAccess(['project:all:view'])){

            if( !$this->hasAccess(['project:own:view']) ){
                return view('access_denied');
            }

            if($project->created_by != $user->id){
                return view('access_denied');
            }
        }

        if($request->header('X-STUDIO-MODE')){
            return view('project_studio/screen/project/display',[
                'project' => $project
            ]);
        }

        return view('project/display',[
            'project' => $project
        ]);
    }


    public function studio_display($id){

        $id = (int) $id;

        $project = Project::findOrFail($id);

        $user = auth()->user();

        if(!$this->hasAccess(['project:all:view'])){

            if( !$this->hasAccess(['project:own:view']) ){
                return view('access_denied');
            }

            if($project->created_by != $user->id){
                return view('access_denied');
            }
        }

        return view('project_studio/display',[
            'project'           => $project,
            'unit_options'      => Unit::toOptions()
        ]);
    }


    public function list(){

        return view('project/list');
    }


    public function _create(Request $request){

        
        if(!$this->hasAccess(['project:own:create'])){

            return response()->json([
                'status'    => 0,
                'message'   => 'Access Denied',
                'data'      => []
            ]);
        }

        $name   = $request->input('name') ?? '';
        $status = $request->input('status');

        $validator = Validator::make($request->all(),[
            'name' => [
                'required',
                'max:255',
                Rule::unique('projects')->where(
                    function ($query) use ($name) {
                        return $query
                        ->where('name', $name)
                        ->where('deleted_at',null);
                }),
            ],
            'status' => [
                'required',
                'max:4'
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => -2,
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
                Rule::unique('projects')->where(
                    function ($query) use ($name,$id) {
                        return $query
                        ->where('name', $name)
                        ->where('id','!=',$id)
                        ->where('deleted_at',null);
                }),
            ],
            'status' => [
                'required',
                'max:4'
            ]
        ]);

        if ($validator->fails()) {
            
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $user    = auth()->user();
        $user_id = $user->id;
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

        if(!$this->hasAccess(['project:all:update'])){

            if(!$this->hasAccess(['project:own:update'])){
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Access Denied',
                    'data'      => []
                ]);
            }
            
            if($project->created_by != $user->id){
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Access Denied',
                    'data'      => []
                ]);
            }
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
        $status     = $request->input('status')         ?? '';
        $result = [];

        $project = new Project();

        $project = $project->where('deleted_at',null);

        if($query != ''){
            $project = $project->where('name','LIKE','%'.$query.'%');
        }

        if($status != ''){
            $project = $project->where('status','=',$status);
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
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $project = Project::find($id);

        $user = auth()->user();

        if(!$this->hasAccess(['project:all:delete'])){

            if(!$this->hasAccess(['project:own:delete'])){
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Access Denied',
                    'data'      => []
                ]);
            }
            
            if($project->created_by != $user->id){
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Access Denied',
                    'data'      => []
                ]);
            }
        }

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

    /*
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
                'status'    => -2,
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
    }*/
}
