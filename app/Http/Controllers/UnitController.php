<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unit;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;


class UnitController extends Controller
{
    public function create(){

        if(!$this->hasAccess('unit:all:create')){
            return view('access_denied');
        }

        return view('unit/create');
    }

    public function display($id){

        $id = (int) $id;

        $unit = Unit::findOrFail($id);

        $user = auth()->user();

        if(!$this->hasAccess(['unit:all:view'])){

            if( !$this->hasAccess(['unit:own:view']) ){
                return view('access_denied');
            }

            if($unit->created_by != $user->id){
                return view('access_denied');
            }
        }
        
        return view('unit/display',[
            'unit' => $unit
        ]);
    }


    public function list(){

        return view('unit/list');
    }


    public function _create(Request $request){

        if(!$this->hasAccess('unit:all:create')){
            return response()->json([
                'status'    => 0,
                'message'   => 'Access Denied',
                'data'      => [] 
            ]);
        }

        $text   = $request->input('text') ?? '';

        $validator = Validator::make($request->all(),[
            'text' => [
                'required',
                'max:255',
                'unique:units'
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

        $unit = new Unit();

        $unit->text          = $text;
        $unit->created_by    = $user_id;
    

        $unit->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id'=> $unit->id
            ]
        ]);

    }

    public function _update(Request $request){

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
                Rule::unique('units')->ignore($id),
            ]
        ]);

        if ($validator->fails()) {
            
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $unit    = Unit::find($id);

        if(!$unit){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }

        $user = auth()->user();

        if(!$this->hasAccess(['unit:all:view'])){

            if( !$this->hasAccess(['unit:own:view']) ){
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Access Denied',
                    'data'      => []
                ]);

            }

            if($unit->created_by != $user->id){
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Access Denied',
                    'data'      => []
                ]);

            }
        }

        $unit->text                         = $text;
        $unit->updated_by                   = $user->id;

        $unit->save();


        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id' => $unit->id
            ]
        ]);

    }

    public function _list(Request $request){

        $page       = (int) $request->input('page')     ?? 1;
        $limit      = (int) $request->input('limit')    ?? 10;
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $query      = $request->input('query')          ?? '';
        $result = [];

        $unit = new Unit();

        if($query != ''){
            $unit = $unit->where('text','LIKE','%'.$query.'%');
        }

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $unit->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $unit->orderBy($orderBy,$order)->take($limit)->get();
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

        $unit = Unit::find($id);

        if(!$unit){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }

        $user = auth()->user();

        if(!$this->hasAccess(['unit:all:view'])){

            if( !$this->hasAccess(['unit:own:view']) ){
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Access Denied',
                    'data'      => []
                ]);

                //return view('access_denied');
            }

            if($unit->created_by != $user->id){
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Access Denied',
                    'data'      => []
                ]);

                //return view('access_denied');
            }
        }

        DB::beginTransaction();

        try {

            $unit->deleted_by = $user->id;
            
            $unit->save();

            //Soft delete
            $unit->delete();
              
            DB::commit();

        }catch(\Exception $e){

            return response()->json([
                'status'    => 0,
                'message'   => $e->getMessage(),
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
