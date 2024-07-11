<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Section;
use App\Models\Unit;
use App\Models\ContractItem;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;


class ContractItemController extends Controller
{
    public function create($section_id){

        $section_id = (int) $section_id;

        $section = Section::findOrFail($section_id);
        $project = $section->Project;
        
        return view('contract_item/create',[
            'project' => $project,
            'section' => $section,
            'unit_options' => Unit::toOptions()
        ]);
    }

    public function display($id){

        $id = (int) $id;

        $contract_item  = ContractItem::findOrFail($id);
        $section        = $contract_item->Section;
        $project        = $section->project;

        $components = $contract_item->components()->orderBy('id','ASC')->get();
        
        
        $unit_options = Unit::toOptions();


        return view('contract_item/display',[
            'contract_item'    => $contract_item,
            'section'          => $section,
            'project'          => $project,
            'components'       => $components,
            'unit_options'     => $unit_options
        ]);
    }


    public function list(){

        return view('contract_item/list');
    }

    public function print($id){
        
        $section = Section::findOrFail($id);

        $components = $section->Components;

        $unit_options = Unit::toOptions();
        echo 'asdsad';
        return view('section/print',[
            'section'          => $section,
            'components'       => $components,
            'unit_options'     => $unit_options
        ]);
    }


    public function _create(Request $request){

        //todo check role

        $item_code                = $request->input('item_code') ?? '';
        $description              = $request->input('description') ?? '';
        $contract_quantity        = $request->input('contract_quantity') ?? '';
        $contract_unit_price      = $request->input('contract_unit_price') ?? '';
        $ref_1_quantity           = $request->input('ref_1_quantity') ?? '';
        $ref_1_unit_price         = $request->input('ref_1_unit_price') ?? '';
        $unit_id                  = (int) $request->input('unit_id') ?? 0;
        $section_id               = (int) $request->input('section_id') ?? 0;

        //TODO check if project exists;

        $validator = Validator::make($request->all(),[
            'item_code' => [
                'required',
                'max:255',
                Rule::unique('contract_items')->where(
                function ($query) use ($section_id,$item_code) {
                    return $query
                    ->where('section_id', $section_id)
                    ->where('item_code', $item_code);
                }),
            ],
            'section_id'      =>[
                'required',
                'integer',
                'gte:1'
            ],
            'description' => [
                'required',
            ],
            'unit_id'      =>[
                'required',
                'integer',
                'gte:1'
            ],
            
            'contract_quantity'     =>[
                'required',
                'numeric'
            ],
            'contract_unit_price'   =>[
                'required',
                'numeric'
            ],
            'ref_1_quantity'        =>[
                'numeric'
            ],
            'ref_2_unit_price'      =>[
                'numeric'
            ],

            
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        

        $user_id = Auth::user()->id;

        $contract_item = new ContractItem();

        $contract_item->section_id              = $section_id;
        $contract_item->item_code               = $item_code;
        $contract_item->description             = $description;
        $contract_item->contract_quantity       = $contract_quantity;
        $contract_item->contract_unit_price     = $contract_unit_price;
        $contract_item->ref_1_quantity          = $ref_1_quantity;
        $contract_item->ref_1_unit_price        = $ref_1_unit_price;
        $contract_item->unit_id                 = $unit_id;
        $contract_item->created_by              = $user_id;

        $contract_item->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id'=> $contract_item->id
            ]
        ]);

    }

    public function _update(Request $request){

        //todo check role

        $item_code                = $request->input('item_code') ?? '';
        $description              = $request->input('description') ?? '';
        $contract_quantity        = $request->input('contract_quantity') ?? '';
        $contract_unit_price      = $request->input('contract_unit_price') ?? '';
        $ref_1_quantity           = $request->input('ref_1_quantity') ?? '';
        $ref_1_unit_price         = $request->input('ref_1_unit_price') ?? '';
        $unit_id                  = (int) $request->input('unit_id') ?? 0;
        $id                       = (int) $request->input('id') ?? 0;
        $section_id               = (int) $request->input('section_id') ?? 0;

        $validator = Validator::make($request->all(),[
            'item_code' => [
                'required',
                'max:255',
                Rule::unique('contract_items')->where(
                function ($query) use ($section_id,$item_code,$id) {
                    return $query
                    ->where('section_id', $section_id)
                    ->where('item_code', $item_code)
                    ->where('id','!=',$id);
                }),
            ],
            'description' => [
                'required',
            ],
            'section_id' =>[
                'required',
                'integer',
                'gte:1'
            ],
            'unit_id'      =>[
                'required',
                'integer',
                'gte:1'
            ],
            
            'contract_quantity'     =>[
                'required',
                'numeric'
            ],
            'contract_unit_price'   =>[
                'required',
                'numeric'
            ],
            'ref_1_quantity'        =>[
                'numeric'
            ],
            'ref_2_unit_price'      =>[
                'numeric'
            ],

        ]);

        if ($validator->fails()) {
            
            return response()->json([
                'status'    => 0,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $user_id        = Auth::user()->id;
        $contract_item  = ContractItem::find($id);

        if(!$contract_item){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => [
                    'id' => $id
                ]
            ]);
        }

        $contract_item->item_code               = $item_code;
        $contract_item->description             = $description;
        $contract_item->contract_quantity       = $contract_quantity;
        $contract_item->contract_unit_price     = $contract_unit_price;
        $contract_item->ref_1_quantity          = $ref_1_quantity;
        $contract_item->ref_1_unit_price        = $ref_1_unit_price;
        $contract_item->unit_id                 = $unit_id;
        $contract_item->updated_by              = $user_id;

        $contract_item->save();


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

        $section_id = (int) $request->input('section_id') ?? 0;
        $page       = (int) $request->input('page')     ?? 1;
        $limit      = (int) $request->input('limit')    ?? 0;
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $query      = $request->input('query')          ?? '';
        $result     = [];

        $contract_item = new ContractItem();

        $contract_item = $contract_item->where('section_id',$section_id);

        if($query != ''){
            $contract_item = $contract_item->where('name','LIKE','%'.$query.'%');
        }

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $contract_item->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $contract_item->orderBy($orderBy,$order)->get();
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
 
         $contract_item = ContractItem::find($id);
 
         if(!$contract_item){
             return response()->json([
                 'status'    => 0,
                 'message'   => 'Record not found',
                 'data'      => []
             ]);
         }
         
         if(!$contract_item->delete()){
            return response()->json([
                'status'    => 0,
                'message'   => 'Delete operation failed',
                'data'      => $e
            ]);
         }

         return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
        
    }
}
