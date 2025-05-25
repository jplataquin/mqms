<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccessCode;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AccessCodeController extends Controller
{
    public function create(){
        
        // if(!$this->hasAccess(['access_code:all:create'])){

        //     return view('access_denied');
        // }

        return view('access_code/create');
    }

    public function display($id){

        $id = (int) $id;

        $accessCode = AccessCode::findOrFail($id);
        
            
        // if(!$this->hasAccess(['access_code:all:view'])){

        //     return view('access_denied');
        // }

        return view('access_code/display',$accessCode);
    }

    public function list(){
        return view('access_code/list');
    }

    public function _create(Request $request){
            
        // if(!$this->hasAccess(['access_code:all:create'])){

        //     return response()->json([
        //         'status'    => 0,
        //         'message'   => 'Access Denied',
        //         'data'      => []
        //     ]);

        // }

        $resource     = $request->input('resource');
        $scope        = $request->input('scope');
        $actions      = $request->input('actions');

        $validator1 = Validator::make($request->all(),[
            'resource' => [
                'required',
            ],
            'scope' => [
                'required',
            ],
            'actions' => [
                'required',
                'json'
            ]
        ]);

        if ($validator1->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator1->messages()
            ]);
        }

        $resource     = trim($resource);
        $resource    = str_replace(' ', '_', $resource);
        
        $actions    = json_decode($actions);

        if(!$actions){
            return response()->json([
                'status'    => 0,
                'message'   => 'No actions defined',
                'data'      => []
            ]);
        }

        foreach($actions as $action){

            $access_code = strtolower($resource.':'.$scope.':'.$action->value);

            if(trim($action->description) == ''){
                return response()->json([
                    'status'    => 0,
                    'message'   => '"'.$action->value.'" description is required',
                    'data'      => []
                ]);
            }

            $exists = AccessCode::where('code', $access_code)->first();


            if($exists){
                 return response()->json([
                    'status'    => 0,
                    'message'   => '"'.$access_code.'" already exists',
                    'data'      => []
                ]);
            }

        }


        foreach($actions as $action){

            $access_code = strtolower($resource.':'.$scope.':'.$action->value);

            $accessCode = new AccessCode();

            $accessCode->code                   = $access_code;
            $accessCode->description            = $action->description;


            $accessCode->save();
        }

    
        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);

    }

    public function _update(Request $request){
        
            
        // if(!$this->hasAccess(['access_code:all:update'])){

        //     return response()->json([
        //         'status'    => 0,
        //         'message'   => 'Access Denied',
        //         'data'      => []
        //     ]);

        // }

        $id                  = (int) $request->input('id') ?? 0;
        $code                = $request->input('code') ?? '';
        $description         = $request->input('description');
        

        $validator = Validator::make($request->all(),[
            'id'   => [
                'required',
                'integer',               
            ],
            'code' => [
                'required',
                Rule::unique('access_codes')->where(
                function ($query) use ($code,$id) {
                    return $query
                    ->where('code', $code)
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

        $accessCode  = AccessCode::find($id);

        if(!$accessCode){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => [
                    'id' => $id
                ]
            ]);
        }

        $accessCode->code                         = $code;
        $accessCode->description                  = $description;
        $accessCode->save();


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

        $accessCode = new AccessCode();

        if($query != ''){
            $accessCode = $accessCode->where('code','LIKE','%'.$query.'%');
        }

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $accessCode->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $accessCode->orderBy($orderBy,$order)->take($limit)->get();
        }

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> $result
        ]);
    }

    public function _delete(Request $request){ 
         
            
        // if(!$this->hasAccess(['access_code:all:delete'])){

        //     return response()->json([
        //         'status'    => 0,
        //         'message'   => 'Access Denied',
        //         'data'      => []
        //     ]);

        // }
        
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
 
         $accessCode = AccessCode::find($id);
 
         if(!$accessCode){
             return response()->json([
                 'status'    => 0,
                 'message'   => 'Record not found',
                 'data'      => []
             ]);
         }
         
        
        if(!$accessCode->delete()){

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