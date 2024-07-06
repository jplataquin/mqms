<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Component;
use App\Models\ComponentItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ComponentItemController extends Controller
{   
    public function display($id){

        $componentItem = ComponentItem::findOrFail($id);

        $component       = $componentItem->Component;
        $section         = $component->Section;
        $project         = $section->Project;

        return view('component/display',[
            'project'           => $project,
            'section'           => $section,
            'component'         => $component
        ]);
    }


    public function _create(Request $request){

        //todo check role

        $name              = $request->input('name') ?? '';
        $quantity          = $request->input('quantity') ?? '';
        $budget_price      = $request->input('budget_price') ?? '';
        $component_unit_id = (int) $request->input('component_unit_id') ?? 0;
        $component_id      = (int) $request->input('component_id');
        $function_type_id  = (int) $request->input('function_type_id');
        $function_variable = $request->input('function_variable');
        
        $validator = Validator::make($request->all(),[
            'name' => [
                'required',
                'max:255',
                Rule::unique('component_items')->where(
                    function ($query) use ($name,$component_id) {
                        return $query
                        ->where('component_id', $component_id)
                        ->where('name', $name)
                        ->where('deleted_at',null);
                }),
            ],
            'component_unit_id' => [
                'required',
                'gte:1',
                'integer'
            ],
            'quantity' => [
                'required',
                'numeric',
                'gt:0'
            ],
            'budget_price' => [
                'required',
                'numeric',
                'gt:0'
            ],
            'function_type_id' =>[
                'required',
                'gte:1',
                'integer'
            ],
            'function_variable'=>[
                'required',
                'numeric'
            ],
            'component_id' => [
                'required',
                'integer',
                'gte:1'
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }


        $component = Component::find($component_id);

        if(!$component){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }

        $user_id = Auth::user()->id;

        $componentItem = new ComponentItem();

        $componentItem->component_id              = $component_id;
        $componentItem->name                      = $name;
        $componentItem->budget_price              = $budget_price;
        $componentItem->quantity                  = $quantity;
        $componentItem->component_unit_id         = $component_unit_id;
        $componentItem->function_type_id          = $function_type_id;
        $componentItem->function_variable         = $function_variable;
        $componentItem->created_by                = $user_id;

        $componentItem->save();


        if($component->status != 'PEND'){
            $component->status      = 'PEND';
            $component->updated_by  = $user_id;
            $component->updated_at  = Carbon::now();
            $component->save();
        }

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id'=> $componentItem->id
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

        $componentItem = ComponentItem::find($id);

        if(!$componentItem){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }

        $componentItem->loadCount('materialQuantities');

        
        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => $componentItem
        ]);
    }

    public function _update(Request $request){

         //todo check role

         $name              = $request->input('name') ?? '';
         $budget_price      = $request->input('budget_price') ?? '';
         $quantity          = $request->input('quantity') ?? '';
         $id                = (int) $request->input('id');
         $component_id      = (int) $request->input('component_id');
         $function_type_id  = (int) $request->input('function_type_id');
         $component_unit_id = (int) $request->input('component_unit_id') ?? 0;
         $function_variable = $request->input('function_variable');

         $validator = Validator::make($request->all(),[
             'name' => [
                 'required',
                 'max:255',
                 Rule::unique('component_items')->where(
                     function ($query) use ($name,$component_id,$id) {
                         return $query
                         ->where('component_id', $component_id)
                         ->where('id','!=',$id)
                         ->where('name', $name)
                         ->where('deleted_at',null);
                 }),
             ],
             'component_unit_id' => [
                 'required',
                 'integer',
                 'gte:1'
             ],
             'quantity' => [
                 'required',
                 'numeric'
             ],
             'budget_price' => [
                 'required',
                 'numeric'
             ],
             'component_id' => [
                'required',
                'integer',
                'gte:1'
            ],
             'id'            => [
                'required',
                'integer',
                'gte:1'
            ],
            'function_type_id' =>[
                'required',
                'integer',
                'gte:1'
            ],
            'function_variable' =>[
                'required',
                'numeric'
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
 
         $componentItem = ComponentItem::find($id);

         if(!$componentItem){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }

        if($componentItem->component_id != $component_id){
            return response()->json([
                'status'    => 0,
                'message'   => 'Component ID does not match',
                'data'      => []
            ]);
        }

        //Get all previous material quantities and check if they are within budget with regards to the new data.
        $material_quantities = $componentItem->MaterialQuantities;


        foreach($material_quantities as $mq){

            if($quantity < ($mq->equivalent * $mq->quantity)){
                
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Error: One or more material quantities are not aligned with the new budget',
                    'data'      => []
                ]);

            }
        }
 
         $componentItem->name                   = $name;
         $componentItem->quantity               = $quantity;
         $componentItem->budget_price           = $budget_price;
         $componentItem->component_unit_id      = $component_unit_id;
         $componentItem->function_type_id       = $function_type_id;
         $componentItem->function_variable      = $function_variable;
         $componentItem->updated_by             = $user_id;
 
         $componentItem->save();
 
         $component = $componentItem->component;
 
         if($component->status != 'PEND'){
             $component->status      = 'PEND';
             $component->updated_by  = $user_id;
             $component->updated_at  = Carbon::now();
             $component->save();
         }
 
         return response()->json([
             'status'    => 1,
             'message'   => '',
             'data'      => [
                 'id'=> $componentItem->id
             ]
         ]);
    }

    public function _delete(Request $request){

        //Check role
        $id      = (int) $request->input('id');
        $user_id = Auth::user()->id;
        

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

        $componentItem = ComponentItem::find($id);

        if(!$componentItem){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }
        
        $component = $componentItem->component;

        if(!$componentItem->delete()){

            return response()->json([
                'status'    => 0,
                'message'   => 'Delete operation failed',
                'data'      => []
            ]);
        }

        
        
        if($component->status != 'PEND'){
            $component->status      = 'PEND';
            $component->updated_by  = $user_id;
            $component->updated_at  = Carbon::now();
            $component->save();
        }

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }
   
}
