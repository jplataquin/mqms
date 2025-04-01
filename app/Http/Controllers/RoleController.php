<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Role;
use App\Models\AccessCode;
use App\Models\RoleAccessCode;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function create(){

        return view('role/create');
    }

    public function display($id){

        $id             = (int) $id;
        $role           = Role::findOrFail($id); 
        $accessCodes    = AccessCode::orderBy('code','ASC')->get();

        return view('role/display',[
            'role'          => $role,
            'accessCodes'   => $accessCodes
        ]);
    }

    public function list(){
        return view('role/list');
    }

    public function _create(Request $request){

        $name         = $request->input('name');
        $description  = $request->input('description');

        $validator = Validator::make($request->all(),[
            'name' => [
                'required',
                'unique:roles'
            ],
            'description' => ['max:300']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $role = new Role();

        $role->name                   = $name;
        $role->description            = $description;

        $role->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id'=> $role->id
            ]
        ]);

    }

    public function _update(Request $request){
        
        $id                  = (int) $request->input('id') ?? 0;
        $name                = $request->input('name') ?? '';
        $description         = $request->input('description');
        

        $validator = Validator::make($request->all(),[
            'id'   => [
                'required',
                'integer',               
            ],
            'name' => [
                'required',
                'max:64',
                Rule::unique('roles')->where(
                function ($query) use ($name,$id) {
                    return $query
                    ->where('name', $name)
                    ->where('id','!=',$id);
                }),
            ],
            'description' => ['required','max:300']
        ]);

        if ($validator->fails()) {
            
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $role  = Role::find($id);

        if(!$role){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => [
                    'id' => $id
                ]
            ]);
        }

        $role->name                         = $name;
        $role->description                  = $description;
        $role->save();


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

        $role = new Role();

        if($query != ''){
            $role = $role->where('code','LIKE','%'.$query.'%');
        }

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $role->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $role->orderBy($orderBy,$order)->take($limit)->get();
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
                 'status'    => -2,
                 'message'   => 'Failed Validation',
                 'data'      => $validator->messages()
             ]);
         }
 
         $role = Role::find($id);
 
         if(!$role){
             return response()->json([
                 'status'    => 0,
                 'message'   => 'Record not found',
                 'data'      => []
             ]);
         }
         
        
        if(!$role->delete()){

            return response()->json([
                'status'    => 0,
                'message'   => 'Delete operation failed',
                'data'      => []
            ]);
        }


        
        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
         
    }

    public function _access_code_remove(Request $request){
    
        $role_id           = (int) $request->input('role_id');
        $access_code_id    = (int) $request->input('access_code_id');

        $validator = Validator::make($request->all(),[
            'role_id' => [
                'required',
                'integer'
            ],
            'access_code_id' => [
                'required',
                'integer'
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        DB::table('role_access_codes')
        ->where('role_id',$role_id)
        ->where('access_code_id',$access_code_id)->delete();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
        
    }

    public function _access_code_add(Request $request){
        $role_id         = (int) $request->input('role_id');
        $access_code_id  = (int) $request->input('access_code_id');

        $validator = Validator::make($request->all(),[
            'role_id' => [
                'required'
            ],
            'access_code_id' => [
                'required',
                Rule::unique('role_access_codes')->where(function ($query) use($role_id,$access_code_id) {
                    return $query->where('role_id', $role_id)
                    ->where('access_code_id', $access_code_id);
                })
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $roleAccessCode = new RoleAccessCode();

        $roleAccessCode->role_id                   = $role_id;
        $roleAccessCode->access_code_id            = $access_code_id;

        $roleAccessCode->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id'=> $roleAccessCode->id
            ]
        ]);
    }


    public function _access_codes($role_id){

        $role_id    = (int) $role_id;
        
        $result = DB::table('role_access_codes')
        ->join('access_codes', 'role_access_codes.access_code_id', '=', 'access_codes.id')
        ->where('role_access_codes.role_id',$role_id)->get();

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> $result
        ]);
    }
}