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
use App\Http\Traits\BudgetTrait;

class ContractItemController extends Controller
{
    use BudgetTrait;

    public function _print($id){

        $contract_item = ContractItem::findOrFail($id);
        
        $section_id         = $contract_item->section_id;
        $contract_item_id   = $contract_item->id;
        echo 'POP';exit;
        return $this->print($section_id,$contract_item_id);
    }
    
    public function create($section_id){

        $section_id = (int) $section_id;

        $section = Section::findOrFail($section_id);
        $project = $section->Project;
        
        $contract_items = ContractItem::where('section_id',$section_id)->get();

        return view('contract_item/create',[
            'project'           => $project,
            'section'           => $section,
            'contract_items'    => $contract_items, 
            'unit_options'      => Unit::toOptions()
        ]);
    }

    public function display($id,Request $request){

        $id = (int) $id;

        $contract_item  = ContractItem::findOrFail($id);
        $section        = $contract_item->Section;
        $project        = $section->project;
        

        $components = $contract_item->components()->orderBy('id','ASC')->get();
        
        
        $unit_options = Unit::toOptions();

        if($request->header('X-STUDIO-MODE')){
            return view('project_studio/screen/contract_item/display',[
                'contract_item'    => $contract_item,
                'section'          => $section,
                'project'          => $project,
                'components'       => $components,
                'unit_options'     => $unit_options
            ]);
        }

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

    // public function print($id){
        
    //     $section = Section::findOrFail($id);

    //     $components = $section->Components;

    //     $unit_options = Unit::toOptions();
      
    //     return view('section/print',[
    //         'section'          => $section,
    //         'components'       => $components,
    //         'unit_options'     => $unit_options,
    //     ]);
    // }


    public function _create(Request $request){

        //todo check role

        $item_code                = $request->input('item_code') ?? '';
        $description              = $request->input('description') ?? '';
        $item_type                = $request->input('item_type') ?? '';
        $contract_quantity        = $request->input('contract_quantity') ?? '';
        $contract_unit_price      = $request->input('contract_unit_price') ?? '';
        $ref_1_quantity           = $request->input('ref_1_quantity') ?? '';
        $ref_1_unit_price         = $request->input('ref_1_unit_price') ?? '';
        $ref_1_unit_id            = (int) $request->input('ref_1_unit_id') ?? 0;

        $budget_quantity           = $request->input('budget_quantity') ?? null;
        $budget_unit_price         = $request->input('budget_unit_price') ?? null;
        $budget_unit_id            = (int) $request->input('budget_unit_id') ?? null;

        $unit_id                  = (int) $request->input('unit_id') ?? 0;
        $section_id               = (int) $request->input('section_id') ?? 0;
       // $parent_contract_item_id  = (int) $request->input('parent_contract_item_id') ?? 0;

        //TODO check if project exists;

        $validator = Validator::make($request->all(),[
            'item_code' => [
                'required',
                'max:255',
                Rule::unique('contract_items')->where(
                function ($query) use ($section_id,$item_code) {
                    return $query
                    ->where('section_id', $section_id)
                    ->where('item_code', $item_code)
                    ->where('deleted_at',null);
                }),
            ],
            'section_id'=>[
                'required',
                'integer',
                'gte:1'
            ],
            'description' => [
                'required',
            ],
            'item_type' =>[
                'required'
            ],
            'unit_id'      =>[
                'required',
                'integer',
                'gte:1'
            ],
            'contract_quantity'=>[
                'required',
                'numeric',
                'gt:0',
            ],
            'contract_unit_price'=>[
                'required',
                'numeric',
                'gt:0',
            ],
            'ref_1_quantity'=>[
                'nullable',
                'numeric',
                'gt:0',
                'required_with:ref_1_unit_id'
            ],
            'ref_1_unit_id' =>[
                'nullable',
                'numeric',
                'gte:1',
                'required_with:ref_1_quantity'
            ],
            'ref_1_unit_price'=>[
                'nullable',
                'numeric'
            ],

            'budget_quantity'=>[
                'nullable',
                'numeric',
                'gt:0',
                'required_with:ref_1_unit_id'
            ],
            'budget_unit_id' =>[
                'nullable',
                'numeric',
                'gte:1',
                'required_with:ref_1_quantity'
            ],
            'budget_unit_price'=>[
                'nullable',
                'numeric'
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

        $contract_item = new ContractItem();

        $contract_item->section_id              = $section_id;
        $contract_item->item_type               = $item_type;
        $contract_item->item_code               = $item_code;
        $contract_item->description             = $description;
        $contract_item->contract_quantity       = $contract_quantity;
        $contract_item->unit_id                 = $unit_id;
        $contract_item->contract_unit_price     = $contract_unit_price;

        if($ref_1_quantity){
            $contract_item->ref_1_quantity          = $ref_1_quantity;
            $contract_item->ref_1_unit_id           = $ref_1_unit_id;
        }

        if($ref_1_unit_price){

            $contract_item->ref_1_unit_price        = $ref_1_unit_price;
        }

        if($budget_quantity){
            $contract_item->budget_quantity          = $budget_quantity;
            $contract_item->budget_unit_id           = $budget_unit_id;
        }

        if($budget_unit_price){

            $contract_item->budget_unit_price        = $budget_unit_price;
        }


        $contract_item->created_by              = $user_id;

        
        $contract_item->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => $contract_item
        ]);

    }

    public function _update(Request $request){

        //todo check role

        $item_code                = $request->input('item_code') ?? '';
        $item_type                = $request->input('item_type') ?? '';
        $description              = $request->input('description') ?? '';
        $contract_quantity        = $request->input('contract_quantity') ?? '';
        $contract_unit_price      = $request->input('contract_unit_price') ?? '';
        
        $ref_1_quantity           = $request->input('ref_1_quantity') ?? '';
        $ref_1_unit_price         = $request->input('ref_1_unit_price') ?? '';
        $ref_1_unit_id            = (int) $request->input('ref_1_unit_id') ?? 0;
        
        $budget_quantity           = $request->input('budget_quantity') ?? '';
        $budget_unit_price         = $request->input('budget_unit_price') ?? '';
        $budget_unit_id            = (int) $request->input('budget_unit_id') ?? 0;
        
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
            'item_type' => [
                'required'
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

            'ref_1_quantity'=>[
                'nullable',
                'numeric',
                'gt:0',
                'required_with:ref_1_unit_id'
            ],
            'ref_1_unit_id' =>[
                'nullable',
                'numeric',
                'gte:1',
                'required_with:ref_1_quantity'
            ],
            'ref_1_unit_price'=>[
                'nullable',
                'numeric'
            ],


            'budget_quantity'=>[
                'nullable',
                'numeric',
                'gt:0',
                'required_with:budget_unit_id'
            ],
            'budget_unit_id' =>[
                'nullable',
                'numeric',
                'gte:1',
                'required_with:budget_quantity'
            ],
            'budget_unit_price'=>[
                'nullable',
                'numeric'
            ]

        ]);

        if ($validator->fails()) {
            
            return response()->json([
                'status'    => -2,
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
        $contract_item->item_type               = $item_type;
        $contract_item->contract_quantity       = $contract_quantity;
        $contract_item->contract_unit_price     = $contract_unit_price;
        $contract_item->unit_id                 = $unit_id;
        $contract_item->updated_by              = $user_id;

        if($ref_1_quantity){
            $contract_item->ref_1_quantity          = $ref_1_quantity;
            $contract_item->ref_1_unit_id           = $ref_1_unit_id;
        }else{
            $contract_item->ref_1_quantity          = null;
            $contract_item->ref_1_unit_id           = null;
        }

        if($ref_1_unit_price){
            $contract_item->ref_1_unit_price        = $ref_1_unit_price;
        }else{
            $contract_item->ref_1_unit_price        = null;
        }

        if($budget_quantity){
            $contract_item->budget_quantity          = $budget_quantity;
            $contract_item->budget_unit_id           = $budget_unit_id;
        }else{
            $contract_item->budget_quantity          = null;
            $contract_item->budget_unit_id           = null;
        }

        if($budget_unit_price){
            $contract_item->budget_unit_price        = $budget_unit_price;
        }else{
            $contract_item->budget_unit_price        = null;
        }

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

        if($section_id){
            $contract_item = $contract_item->where('section_id',$section_id);
        }

        if($query != ''){
            $contract_item = $contract_item->where('name','LIKE','%'.$query.'%');
        }

        //Filter out deleted records
        $contract_item = $contract_item->where('deleted_at','=',null);

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $contract_item->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $contract_item->orderBy($orderBy,$order)->get();
        }

        return response()->json([
            'status'    => 1,
            'message'   =>'',
            'data'      => $result
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
                 'status'    => -2,
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
