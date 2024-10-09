<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MaterialGroup;
use App\Models\MaterialItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MaterialItemController extends Controller
{
    public function create(){

        $materialGroup = new materialGroup();

        $rows = $materialGroup::orderBy('name','ASC')->get();

        return view('material_item/create',[
            'materialGroups' => $rows
        ]);
    }

    public function display($id,Request $request){

        $id = (int) $id;

        $back = $request->input('b');

        $materialItem = MaterialItem::findOrFail($id);
        
        $materialGroup = new materialGroup();

        $materialGroupRows = $materialGroup::orderBy('name','ASC')->get();

        return view('material_item/display',[
            'materialItem'      => $materialItem,
            'materialGroups'    => $materialGroupRows,
            'back'              => $back
        ]);
    }

    public function edit($id){

        return view('material_item/edit');
    }

    public function list(){

        return view('material_item/list');
    }

    public function _create(Request $request){

        //todo check role

        $material_group_id              = $request->input('material_group_id') ?? 0;
        $name                           = $request->input('name') ?? '';
        $specification_unit_packaging   = $request->input('specification_unit_packaging') ?? '';
        $brand                          = $request->input('brand') ?? '';

        $validator = Validator::make($request->all(),[
            'material_group_id'             => ['required','integer'],
            'name'                          => [
                'required',
                'max:255',
                 Rule::unique('material_items')->where(function ($query) 
                 use($material_group_id,$name,$specification_unit_packaging,$brand){
                    return $query
                            ->where('material_group_id',$material_group_id)
                            ->where('name', $name)
                            ->where('specification_unit_packaging', $specification_unit_packaging)
                            ->where('brand', $brand);
                 })
            ],
            'specification_unit_packaging'  => ['required','max:255'],
            'brand'                         => ['max:255']
        ]);

        if ($validator->fails()) {
            
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        

        $user_id = Auth::user()->id;


        $materialItem = new MaterialItem();

        $materialItem->material_group_id            = $material_group_id;
        $materialItem->name                         = $name;
        $materialItem->specification_unit_packaging = $specification_unit_packaging;
        $materialItem->brand                        = $brand;
        $materialItem->created_by                   = $user_id;

        $materialItem->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id'=> $materialItem->id
            ]
        ]);
    }

    public function _update(Request $request){

        //todo check role

        $id                             = $request->input('id') ?? 0;
        $material_group_id              = $request->input('material_group_id') ?? 0;
        $name                           = $request->input('name') ?? '';
        $specification_unit_packaging   = $request->input('specification_unit_packaging') ?? '';
        $brand                          = $request->input('brand') ?? '';

        $validator = Validator::make($request->all(),[
            'id'                            => ['required','integer'],
            'material_group_id'             => ['required','integer'],
            'name'                          => [
                'required',
                'max:255',
                 Rule::unique('material_items')->where(function ($query) 
                 use($material_group_id,$name,$specification_unit_packaging,$brand,$id){
                    return $query
                            ->where('material_group_id', $material_group_id)
                            ->where('name', $name)
                            ->where('specification_unit_packaging', $specification_unit_packaging)
                            ->where('brand', $brand)
                            ->where('id','!=',$id);
                 })
            ],
            'specification_unit_packaging'  => ['required','max:255'],
            'brand'                         => ['max:255']
        ]);

        if ($validator->fails()) {
            
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $user_id      = Auth::user()->id;
        $materialItem = MaterialItem::find($id);

        if(!$materialItem){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => [
                    'id' => $id
                ]
            ]);
        }

        $materialItem->material_group_id            = $material_group_id;
        $materialItem->name                         = $name;
        $materialItem->specification_unit_packaging = $specification_unit_packaging;
        $materialItem->brand                        = $brand;
        $materialItem->updated_by                   = $user_id;

        $materialItem->save();


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

         $page              = (int) $request->input('page')     ?? 1;
         $limit             = (int) $request->input('limit')    ?? 10;
         $orderBy           = $request->input('order_by')       ?? 'id';
         $order             = $request->input('order')          ?? 'DESC';
         $query             = $request->input('query')          ?? '';
         $material_group_id = $request->input('material_group_id');

         $result = [];
 
         $materialItem = new MaterialItem();
 
         if($query != ''){
             $materialItem = $materialItem->where('name','LIKE','%'.$query.'%');
         }

         if($material_group_id){
            $materialItem = $materialItem->where('material_group_id',$material_group_id);
         }
 
         if($limit > 0){
             $page   = ($page-1) * $limit;
             
             $result = $materialItem->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
             
         }else{
 
             $result = $materialItem->orderBy($orderBy,$order)->get();
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
