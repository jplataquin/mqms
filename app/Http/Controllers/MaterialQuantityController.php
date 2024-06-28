<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MaterialItem;
use App\Models\MaterialQuantity;
use App\Models\ComponentItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MaterialQuantityController extends Controller
{

    public function _create(Request $request){

        //Check role

        $component_item_id              = $request->input('component_item_id');
        $material_item_id               = $request->input('material_item_id');
        $equivalent                     = $request->input('equivalent');
        $quantity                       = $request->input('quantity');

        $validator = Validator::make($request->all(),[
            'material_item_id'               => [
                'required',
                'integer',
                Rule::unique('material_quantities')->where(
                    function ($query) use ($component_item_id,$material_item_id) {
                        return $query
                        ->where('component_item_id', $component_item_id)
                        ->where('material_item_id', $material_item_id)
                        ->where('deleted_at',null);
                })
            ],
            'component_item_id'         => ['required','integer'],
            'equivalent'                => ['required','numeric'],
            'quantity'                  => ['required','numeric']
        ]);

        if($validator->fails()){
            
            return response()->json([
                'status'    => 0,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $user_id = Auth::user()->id;


        $materialQuantity = new MaterialQuantity();

        $materialQuantity->component_item_id      = $component_item_id;
        $materialQuantity->material_item_id       = $material_item_id;
        $materialQuantity->quantity               = $quantity;
        $materialQuantity->equivalent             = $equivalent;
       
        $materialQuantity->created_by             = $user_id;

        $materialQuantity->save();

        $component = $materialQuantity->componentItem->component;
        
        //Todo enclosed in a transaction
         if($component->status != 'PEND'){
             $component->status = 'PEND';
             $component->save();
         }

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id'=> $materialQuantity->id
            ]
        ]);
    }

    public function _list(Request $request){

        $component_item_id = (int) $request->input('component_item_id');
         
        //Check if material item exists
        $componentItem  = ComponentItem::find($component_item_id);
       
        if(!$component_item_id){

           return response()->json([
               'status'    => 0,
               'message'   => 'Component item does not exists',
               'data'      => [
                   'id' => $component_item_id
               ]
           ]);

           return false;
        }

        $page       = (int) $request->input('page')     ?? 0;
        $limit      = (int) $request->input('limit')    ?? 10;
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $result     = [];

        $materialQuantity = $componentItem->MaterialQuantities();

    
        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $materialQuantity->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $materialQuantity->orderBy($orderBy,$order)->get();
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
                'status'    => 0,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $materialQuantity = MaterialQuantity::find($id);

        if(!$materialQuantity){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }

        if(!$materialQuantity->delete()){
            return response()->json([
                'status'    => 0,
                'message'   => 'Delete operation failed',
                'data'      => []
            ]);
        }
         
        $component = $materialQuantity->componentItem->component;
        
        if($component->status != 'PEND'){
            $component->status = 'PEND';
            $component->save();
        }

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }
}