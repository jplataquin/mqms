<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ComponentUnit;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;


class ComponentUnitController extends Controller
{
    public function create(){

        return view('component_unit/create');
    }

    public function display($id){

        $id = (int) $id;

        $componentUnit = componentUnit::findOrFail($id);

        return view('component_unit/display',[
            'componentUnit' => $componentUnit
        ]);
    }


    public function list(){

        return view('component_unit/list');
    }


    public function _create(Request $request){

        //todo check role

        $text   = $request->input('text') ?? '';

        $validator = Validator::make($request->all(),[
            'text' => [
                'required',
                'max:255',
                'unique:component_unit'
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

        $componentUnit = new ComponentUnit();

        $componentUnit->text          = $text;
        $componentUnit->created_by    = $user_id;
    

        $componentUnit->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id'=> $componentUnit->id
            ]
        ]);

    }

    public function _update(Request $request){

        //todo check role

        $id       = (int) $request->input('id') ?? 0;
        $text     = $request->input('text') ?? '';
        
        $validator = Validator::make($request->all(),[
            'id'   => [
                'required',
                'integer',               
            ],
            'text' => [
                'required',
                'max:255',
                Rule::unique('component_unit')->ignore($id),
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
        $componentUnit = ComponentUnit::find($id);

        if(!$componentUnit){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => [
                    'id' => $id
                ]
            ]);
        }

        $componentUnit->text                         = $text;
        $componentUnit->updated_by                   = $user_id;

        $componentUnit->save();


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

        $componentUnit = new ComponentUnit();

        if($query != ''){
            $componentUnit = $componentUnit->where('text','LIKE','%'.$query.'%');
        }

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $componentUnit->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $componentUnit->orderBy($orderBy,$order)->take($limit)->get();
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

        $componentUnit = ComponentUnit::find($id);

        if(!$componentUnit){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }
        
        $user_id = Auth::user()->id;
        
        $componentUnit->deleted_by = $user_id;
        
        $componentUnit->save();

        //Soft delete
        if(!$componentUnit->delete()){
           
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
}
