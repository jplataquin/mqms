<?php

namespace App\Http\Controllers;

use App\Helpers\CustomHelpers;
use Illuminate\Http\Request;
use App\Models\MaterialGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MaterialGroupController extends Controller
{
    public function create(){

        return view('material_group/create');
    }

    public function display($id){

        $id = (int) $id;

        $materialGroup = MaterialGroup::findOrFail($id);
        
        return view('material_group/display',$materialGroup);
    }

    public function edit($id){

        return view('material_group/edit');
    }

    public function list(){

        return view('material_group/list');
    }


    public function _create(Request $request){

        //todo check role

        $name = $request->input('name') ?? '';
        
        $validator = Validator::make($request->all(),[
            'name'                          => [
                'required',
                'max:255',
                'unique:material_groups,name'
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


        $materialGroup = new MaterialGroup();

        $materialGroup->name        = $name;
        $materialGroup->created_by  = $user_id;

        $materialGroup->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id'=> $materialGroup->id
            ]
        ]);
    }

    public function _update(Request $request){

        //todo check role

        $id       = $request->input('id') ?? 0;
        $name     = $request->input('name') ?? '';
        
        $validator = Validator::make($request->all(),[
            'id'   => ['required','integer'],
            'name' => [
                'required',
                'max:255',
                'unique:material_groups,name,'.$id
            ]
        ]);

        if ($validator->fails()) {
            
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $user_id      = Auth::user()->id;
        $materialGroup = MaterialGroup::find($id);

        if(!$materialGroup){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => [
                    'id' => $id
                ]
            ]);
        }

        $materialGroup->name         = $name;
        $materialGroup->updated_by   = $user_id;

        $materialGroup->save();


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

        $materialGroup = new MaterialGroup();

        if($query != ''){
            $materialGroup = $materialGroup->where('name','LIKE','%'.$query.'%');
        }

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $materialGroup->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $materialGroup->orderBy($orderBy,$order)->take($limit)->get();
        }

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> $result
        ]);
    }

    public function _delete(Request $request){

    }
}
