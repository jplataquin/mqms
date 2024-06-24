<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MaterialItem;
use App\Models\MaterialItemBrand;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class MaterialItemBrandController extends Controller
{
    
    public function list(Request $request,$material_item_id){

        $materialItem  = MaterialItem::findOrFail($material_item_id);

        return view('material_item/brand/list',[
            'materialItem' => $materialItem,
        ]);
    }

    public function _create(Request $request,$material_item_id){

         //todo check role

         $material_item_id = (int) $material_item_id;
         
         //Check if material item exists
         $materialItem = MaterialItem::find($material_item_id);
        
         if(!$materialItem){

            return response()->json([
                'status'    => 0,
                'message'   => 'Matetrial item does not exists',
                'data'      => [
                    'id' => $material_item_id
                ]
            ]);

            return false;
         }

         $validator = Validator::make($request->all(),[
            'name' => [
                'required',
                'max:255',
                Rule::unique('material_item_brand')->where(
                function ($query) use ($material_item_id,$request) {
                    return $query
                    ->where('material_item_id', $material_item_id)
                    ->where('name', $request->name);
                }),
            ],
        ]);

        if ($validator->fails()) {
            
            return response()->json([
                'status'    => 0,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $name   = $request->input('name') ?? '';
        $user_id = Auth::user()->id;


        $materialItemBrand = new MaterialItemBrand();

        $materialItemBrand->material_item_id = $material_item_id;
        $materialItemBrand->name             = $name;
        $materialItemBrand->created_by       = $user_id;

        $materialItemBrand->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id'=> $materialItemBrand->id
            ]
        ]);
    }

    public function _update(Request $request,$material_item_id,$id){

        $material_item_id = (int) $material_item_id;
         
        //Check if material item exists
        $materialItem = MaterialItem::find($material_item_id);
       
        //Make sure material item exists;
        if(!$materialItem){

           return response()->json([
               'status'    => 0,
               'message'   => 'Matetrial item does not exists',
               'data'      => [
                   'id' => $material_item_id
               ]
           ]);

           return false;
        }


        //Check if brand exists
        $materialItemBrand = MaterialItemBrand::find($id);
       
        //Make sure material item exists;
        if(!$materialItemBrand){

           return response()->json([
               'status'    => 0,
               'message'   => 'Record item does not exists',
               'data'      => [
                   'id' => $id
               ]
           ]);

           return false;
        }

        //If brand mismatch
        if($materialItemBrand->material_item_id != $material_item_id){

            return response()->json([
                'status'    => 0,
                'message'   => 'Brand record mismatch',
                'data'      => [
                    'material_item_id' => $material_item_id,
                    'id' => $id
                ]
            ]);
 
            return false;
        }


        $validator = Validator::make($request->all(),[
            'name' => [
                'required',
                'max:255',
                Rule::unique('material_item_brand')->where(
                function ($query) use ($material_item_id,$request,$id) {
                    return $query
                    ->where('material_item_id', $material_item_id)
                    ->where('name', $request->name)
                    ->where('id','!=',$id);
                }),
            ],
        ]);

        if ($validator->fails()) {
            
            return response()->json([
                'status'    => 0,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $name       = $request->input('name') ?? '';
        $user_id    = Auth::user()->id;

        $materialItemBrand->name        = $name;
        $materialItemBrand->updated_by = $user_id;
        $materialItemBrand->save();

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> $materialItemBrand
        ]);
    }

    public function _list(Request $request,$material_item_id){

        $material_item_id = (int) $material_item_id;
         
        //Check if material item exists
        $materialItem = MaterialItem::find($material_item_id);
       
        if(!$materialItem){

           return response()->json([
               'status'    => 0,
               'message'   => 'Matetrial item does not exists',
               'data'      => [
                   'id' => $material_item_id
               ]
           ]);

           return false;
        }

        $page       = (int) $request->input('page')     ?? 0;
        $limit      = (int) $request->input('limit')    ?? 10;
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $query      = $request->input('query')          ?? '';
        $result = [];

        $materialItemBrand = $materialItem->brands();

        if($query != ''){
            $materialItemBrand = $materialItemBrand->where('name','LIKE','%'.$query.'%');
        }

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $materialItemBrand->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $materialItemBrand->orderBy($orderBy,$order)->take($limit)->get();
        }

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> $result
        ]);
    }

    public function _delete(Request $request, $material_item_id){

        $material_item_id = (int) $material_item_id;
        $id = (int) $request->input('id') ?? 0;

        //Check if material item exists
        $materialItem = MaterialItem::find($material_item_id);
       
        //Make sure material item exists;
        if(!$materialItem){

           return response()->json([
               'status'    => 0,
               'message'   => 'Matetrial item does not exists',
               'data'      => [
                   'id' => $material_item_id
               ]
           ]);

           return false;
        }


        //Check if brand exists
        $materialItemBrand = MaterialItemBrand::find($id);
       
        //Make sure material item exists;
        if(!$materialItemBrand){

           return response()->json([
               'status'    => 0,
               'message'   => 'Record item does not exists',
               'data'      => [
                   'id' => $id
               ]
           ]);

           return false;
        }

        //If brand mismatch
        if($materialItemBrand->material_item_id != $material_item_id){

            return response()->json([
                'status'    => 0,
                'message'   => 'Brand record mismatch',
                'data'      => [
                    'material_item_id' => $material_item_id,
                    'id' => $id
                ]
            ]);
 
            return false;
        }

        $user_id = Auth::user()->id;

        $materialItemBrand->deleted_by = $user_id;
        $materialItemBrand->save();
         
        $materialItemBrand->delete();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);

    }

    
}
