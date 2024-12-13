<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Component;
use App\Models\ComponentItem;
use App\Models\MaterialQuantityRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Fluent;

class ComponentItemController extends Controller
{   
    public function display($id){

        $component_item = ComponentItem::findOrFail($id);

        $component       = $component_item->Component;
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
        $function_variable = $request->input('function_variable');
        $unit_id           = (int) $request->input('unit_id') ?? 0;
        $component_id      = (int) $request->input('component_id');
        $function_type_id  = (int) $request->input('function_type_id');
        $ref_1_unit_id     = (int) $request->input('ref_1_unit_id');
        $ref_1_quantity    = (float) $request->input('ref_1_quantity');
        $ref_1_unit_price  = (float) $request->input('ref_1_unit_price');
        $sum_flag          = (boolean) $request->input('sum_flag');
        
        $rules = [
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

    
            'unit_id' => [
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
        ];

        if($ref_1_quantity > 0){

            $rules['ref_1_quantity'] = [
                'numeric'
            ];

            $rules['ref_1_unit_id'] = [
                'required_with:ref_1_quantity',
                'integer',
                'gte:1'
            ];

            $rules['ref_1_unit_price'] = [
                'required_with:ref_1_quantity',
                'numeric',
                'gt:1'
            ];
        }

        $validator = Validator::make($request->all(),$rules);

        if ($validator->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation '.$ref_1_quantity,
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


        if($ref_1_quantity <= 0){
            $ref_1_quantity     = null;
            $ref_1_unit_id      = null;
            $ref_1_unit_price   = null;
        }
         

        $user_id = Auth::user()->id;

        $component_item = new ComponentItem();

        

        $component_item->component_id              = $component_id;
        $component_item->name                      = $name;
        $component_item->budget_price              = $budget_price;
        $component_item->quantity                  = $quantity;
        $component_item->unit_id                   = $unit_id;
        $component_item->function_type_id          = $function_type_id;
        $component_item->function_variable         = $function_variable;
        $component_item->created_by                = $user_id;
        $component_item->sum_flag                  = $sum_flag;
        $component_item->ref_1_quantity            = $ref_1_quantity;
        $component_item->ref_1_unit_id             = $ref_1_unit_id;
        $component_item->ref_1_unit_price          = $ref_1_unit_price;

        $component_item->save();


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
                'id'=> $component_item->id
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
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $component_item = ComponentItem::find($id);

        if(!$component_item){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }

        $component_item->loadCount('materialQuantities');

        
        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => $component_item
        ]);
    }

    public function _update(Request $request){

         //todo check role

         $name              = $request->input('name') ?? '';
         $budget_price      = $request->input('budget_price') ?? '';
         $quantity          = $request->input('quantity') ?? '';
         $function_variable = $request->input('function_variable');
         $id                = (int) $request->input('id');
         $component_id      = (int) $request->input('component_id');
         $function_type_id  = (int) $request->input('function_type_id');
         $unit_id           = (int) $request->input('unit_id') ?? 0;
         $sum_flag          = (boolean) $request->input('sum_flag');
         $ref_1_unit_id     = (int) $request->input('ref_1_unit_id');
         $ref_1_quantity    = (float) $request->input('ref_1_quantity');
         $ref_1_unit_price  = (float) $request->input('ref_1_unit_price');
        
         $rules = [
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
            'unit_id' => [
                'required',
                'integer',
                'gte:1'
            ],
            'quantity' => [
                'required',
                'numeric',
                'gte:0'
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
         ];

         if($ref_1_quantity > 0){

            $rules['ref_1_quantity'] = [
               'numeric'
            ];

            $rules['ref_1_unit_id'] = [
               'required_with:ref_1_quantity',
               'integer',
               'gte:1'
            ];

            $rules['ref_1_unit_price'] = [
                'numeric',
                'gt:0'
             ];
         }
         

         $validator = Validator::make($request->all(),$rules);
 
         if ($validator->fails()) {
             return response()->json([
                 'status'    => -2,
                 'message'   => 'Failed Validation',
                 'data'      => $validator->messages()
             ]);
         }
 
         $user_id        = Auth::user()->id;
         $component_item = ComponentItem::find($id);

         if(!$component_item){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }

        if($component_item->component_id != $component_id){
            return response()->json([
                'status'    => 0,
                'message'   => 'Component ID does not match',
                'data'      => []
            ]);
        }

        //Get all previous material quantities and check if they are within budget with regards to the new data.
        // $material_quantities = $component_item->MaterialQuantities;


        // foreach($material_quantities as $mq){

        //     if($quantity < ($mq->equivalent * $mq->quantity)){
                
        //         return response()->json([
        //             'status'    => 0,
        //             'message'   => 'Error: One or more material quantities are not aligned with the new budget',
        //             'data'      => []
        //         ]);

        //     }
        // }
 
        if($ref_1_quantity <= 0){
            $ref_1_quantity     = null;
            $ref_1_unit_id      = null;
            $ref_1_unit_price   = null;
        }
         
        
         $component_item->name                   = $name;
         $component_item->quantity               = $quantity;
         $component_item->budget_price           = $budget_price;
         $component_item->unit_id                = $unit_id;
         $component_item->function_type_id       = $function_type_id;
         $component_item->function_variable      = $function_variable;
         $component_item->sum_flag               = $sum_flag;
         $component_item->ref_1_quantity         = $ref_1_quantity;
         $component_item->ref_1_unit_id          = $ref_1_unit_id;
         $component_item->ref_1_unit_price       = $ref_1_unit_price;
         $component_item->updated_by             = $user_id;
 
         $component_item->save();
 
         $component = $component_item->component;
 
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
                 'id'=> $component_item->id
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
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $component_item = ComponentItem::find($id);

        if(!$component_item){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }
        
        $component = $component_item->component;


        //Check if component item has material requests items
        $test_for_existing_request = $component_item->MaterialQuantityRequestItems()->whereIn('status',['APRV','PEND'])->count();

        if($test_for_existing_request){
            return response()->json([
                'status'    => 0,
                'message'   => 'Cannot delete record, the Component Item has associated Material Request',
                'data'      => []
            ]);
        }

        if(!$component_item->delete()){

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


    public function report($id){

        $id = (int) $id;

        $component_item = ComponentItem::findOrFail($id);

        $component      = $component_item->Component;
        $contract_item  = $component->ContractItem;        
        $section        = $component->Section;
        $project        = $section->Project;

        $material_quantity_request_ids = $component_item->MaterialQuantityRequestItems()->select(DB::raw('DISTINCT material_quantity_request_id'))->get();
        
        $material_requests = [
            'APRV' => [],
            'PEND' => [],
            'REJC' => [],
            'DELE' => []
        ];

        foreach($material_quantity_request_ids as $row){
            
            echo $row->material_quantity_request_id.'<br>';

            $material_request = MaterialQuantityRequest::find($row->material_quantity_request_id);

            if(!$material_request){

                echo 'here';
                
                if($material_request->deleted_at != null){
                
                    $material_requests['DELE'][$material_request->id] = $material_request;
                
                }else{

                    echo $material_request->status.' <br>';

                    switch ($material_request->status){
                        case 'APRV':

                            $material_requests['APRV'][$material_request->id] = $material_request;
                
                            break;
                        
                        case 'PEND':

                            $material_requests['PEND'][$material_request->id] = $material_request;
                
                            break;

                        case 'REJC':

                            $material_requests['REJC'][$material_request->id] = $material_request;
                
                            break;
                    }
                }
                 
            }
        }

        print_r($material_requests);
        
        return view('/component_item/report',[
            'component_item'    => $component_item,
            'material_requests' => $material_requests,
            'project'           => $project,
            'section'           => $section,
            'contract_item'     => $contract_item,
            'component'         => $component
        ]);
    }
   
}
