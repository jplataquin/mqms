<?php

namespace App\Http\Controllers;

use App\Helpers\CustomHelpers;
use Illuminate\Http\Request;
use App\Models\Component;
use App\Models\MaterialItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;


class ComponentController extends Controller
{
    public function _create(Request $request){

        //todo check role

        $name               = $request->input('name') ?? '';
        $section_id         = $request->input('section_id');

        $validator = Validator::make($request->all(),[
            'name' => [
                'required',
                'max:255',
                Rule::unique('components')->where(
                    function ($query) use ($name,$section_id) {
                        return $query
                        ->where('section_id', $section_id)
                        ->where('name', $name);
                }),
            ],
            'section_id' => ['required','integer']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $user_id = Auth::user()->id;

        $component = new Component();

        $component->name                   = $name;
        $component->status                 = 'PEND';
        $component->section_id             = $section_id;
        $component->created_by             = $user_id;

        $component->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id'=> $component->id
            ]
        ]);

    }

    public function _retrieve(Request $request){

        //Check role

        $id = $request->input('id');


        $validator = Validator::make($request->all(),[
            'id' => ['required','integer']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $component = Component::find($id);

        if(!$component){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }

        $component->loadCount('componentItems');

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => $component
        ]);
    }

    public function display($id){

        $component = Component::findOrFail($id);

        $section         = $component->Section;
        $project         = $section->Project;
        $materialItems   = MaterialItem::get();
        $componentItems = $component->componentItems()->orderBy('id','ASC')->withCount('materialQuantities')->get();


        $materialArr   = [];
        
        foreach(MaterialItem::get() as $mi){
        
            $materialArr[ $mi->id ] = $mi;
        }

        $hash = generateComponentHash($project,$section,$component,$componentItems,$materialArr);

        return view('component/display',[
            'project'           => $project,
            'section'           => $section,
            'component'         => $component,
            'componentItems'    => $componentItems,
            'materialItems'     => $materialItems,
            'hash'              => $hash
        ]);
    }

    public function preview($id){

        $component = Component::findOrFail($id);

        $section         = $component->section;
        $project         = $section->project;
        $componentItems  = $component->componentItems()->orderBy('id','ASC')->withCount('materialQuantities')->get();

        
        $materialItems   = [];
        
        foreach(MaterialItem::get() as $mi){
        
            $materialItems[ $mi->id ] = $mi;
        }
        
        $hash = generateComponentHash($project,$section,$component,$componentItems,$materialItems);

        return view('component/preview',[
            'project'           => $project,
            'section'           => $section,
            'component'         => $component,
            'componentItems'    => $componentItems,
            'materialItems'     => $materialItems,
            'hash'              => $hash
        ]);
    }

    public function _update(Request $request){

        //todo check role

        $id                  = (int) $request->input('id') ?? 0;
        $name                = $request->input('name') ?? '';
        $section_id          = (int) $request->input('section_id');
        $status              = $request->input('status');

        $validator = Validator::make($request->all(),[
            'id'   => [
                'required',
                'integer',               
            ],
            'name' => [
                'required',
                'max:255',
                Rule::unique('components')->where(
                function ($query) use ($section_id,$id,$name) {
                    return $query
                    ->where('section_id', $section_id)
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

        $user_id    = Auth::user()->id;
        $component  = Component::find($id);

        if(!$component){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => [
                    'id' => $id
                ]
            ]);
        }

        $component->name                         = $name;
        $component->status                       = 'PEND';
        $component->updated_by                   = $user_id;
        $component->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id' => $id,
                'status' => $status
            ]
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
 
         $component = Component::find($id);
 
         if(!$component){
             return response()->json([
                 'status'    => 0,
                 'message'   => 'Record not found',
                 'data'      => []
             ]);
         }
         
        
        if(!$component->delete()){

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


    public function _list(Request $request){

        //todo check role

        $section_id = (int) $request->input('section_id') ?? 0;
        $page       = (int) $request->input('page')     ?? 1;
        $limit      = (int) $request->input('limit')    ?? 0;
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $query      = $request->input('query')          ?? '';
        $result     = [];

        $component = new Component();

        $component = $component->where('section_id',$section_id);

        if($query != ''){
            $component = $component->where('name','LIKE','%'.$query.'%');
        }

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $component->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $component->orderBy($orderBy,$order)->get();
        }

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> $result
        ]);
    }
}
