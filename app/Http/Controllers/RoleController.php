<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Role;
use App\Models\AccessCode;

class RoleController extends Controller
{
    public function create(){

        return view('role/create');
    }

    public function display($id){

        $id             = (int) $id;
        $role           = Role::findOrFail($id); 
        $accessCodes    = AccessCode::get();

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
}