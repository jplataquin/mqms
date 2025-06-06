<?php

namespace App\Http\Controllers;

use App\Helpers\CustomHelpers;
use Illuminate\Http\Request;
use App\Models\Component;
use App\Models\MaterialItem;
use App\Models\Unit;
use App\Models\ComponentItem;
use App\Models\ContractItem; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;
use App\Http\Traits\BudgetTrait;


class ComponentController extends Controller
{
    use BudgetTrait;

    public function _print($id){
        
        $component = Component::findOrFail($id);

        $user = auth()->user();

        if(!$this->hasAccess(['component:all:view'])){

            if( !$this->hasAccess(['component:own:view']) ){
                // return response()->json([
                //     'status'    => 0,
                //     'message'   => 'Access Denied',
                //     'data'      => []
                // ]);

                return view('access_denied');
            }

            if($component->created_by != $user->id){
                // return response()->json([
                //     'status'    => 0,
                //     'message'   => 'Access Denied',
                //     'data'      => []
                // ]);

                return view('access_denied');
            }
        }


        $component_id       = $component->id;
        $contract_item_id   = $component->contract_item_id;
        $section_id         = $component->section_id;
        
        return $this->print($section_id,$contract_item_id,$component_id);
    }

    public function _create(Request $request){

        if(!$this->hasAccess('component:own:create')){
            return response()->json([
                'status'    => 0,
                'message'   => 'Access Denied',
                'data'      => []
            ]);
        }

        $name               = $request->input('name') ?? '';
        $quantity           = $request->input('quantity') ?? 0;
        $contract_item_id   = (int) $request->input('contract_item_id');
        $section_id         = (int) $request->input('section_id');
        $use_count          = (int) $request->input('use_count') ?? 1;
        $unit_id            = (int) $request->input('unit_id');
        $sum_flag           = (boolean) $request->input('sum_flag');

        $ref_1_quantity     = $request->input('ref_1_quantity');
        $ref_1_unit_id      = $request->input('ref_1_unit_id');
        $ref_1_unit_price   = $request->input('ref_1_unit_price');

        $validator = Validator::make($request->all(),[
            'name' => [
                'required',
                'max:255',
                Rule::unique('components')->where(
                    function ($query) use ($name,$section_id) {
                        return $query
                        ->where('section_id', $section_id)
                        ->where('name', $name)
                        ->where('deleted_at',null);
                }),
            ],
            'ref_1_quantity'    =>[
               'nullable',
                'numeric',
                'gt:0',
                'required_with:ref_1_unit_id'
            ],
            'ref_1_unit_id'     =>[
                'nullable',
                'numeric',
                'gte:1',
                'required_with:ref_1_quantity'
            ],
            'ref_1_unit_price'  =>[
                'nullable',
                'numeric'
            ],
            'quantity' =>[
                'required',
                'numeric',
                'not_in:0'
            ],
            'use_count' =>[
                'required',
                'integer',
                'gte:1'
            ],
            'contract_item_id' => [
                'required',
                'integer',
                'gte:1'
            ],
            'section_id' => [
                'required',
                'integer',
                'gte:1'
            ],
            'unit_id' =>[
                'required',
                'integer',
                'gte:1'
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

        $component = new Component();

        $component->name                   = $name;
        $component->contract_item_id       = $contract_item_id;
        $component->quantity               = $quantity;
        $component->unit_id                = $unit_id;
        $component->use_count              = $use_count;
        $component->status                 = 'PEND';
        $component->section_id             = $section_id;
        $component->sum_flag               = $sum_flag;

        $component->ref_1_quantity         = $ref_1_quantity;
        $component->ref_1_unit_id          = $ref_1_unit_id;
        $component->ref_1_unit_price       = $ref_1_unit_price;

        $component->created_by             = $user_id;

        $component->save();


        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => $component
        ]);

    }

    public function _retrieve(Request $request){

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

        $user = auth()->user();

        if(!$this->hasAccess(['component:all:view'])){

            if( !$this->hasAccess(['component:own:view']) ){
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Access Denied',
                    'data'      => []
                ]);
            }

            if($component->created_by != $user->id){
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Access Denied',
                    'data'      => []
                ]);
            }
        }


        $component->loadCount('componentItems');

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => $component
        ]);
    }

    public function display($id,Request $request){

        
        $back = $request->input('b');
        
        $component = Component::findOrFail($id);

        $user = auth()->user();

        if(!$this->hasAccess(['component:all:view'])){

            if( !$this->hasAccess(['component:own:view']) ){
                return view('access_denied');
            }

            if($component->created_by != $user->id){
                return view('access_denied');
            }
        }


        $contract_item   = $component->ContractItem;
        $section         = $component->Section;
        $project         = $section->Project;
        
        $materialItems   = MaterialItem::orderBy('name','ASC')->get();
        $componentItems = $component->componentItems()->orderBy('id','ASC')->withCount('materialQuantities')->get();

        $grand_total_amounts = $section->getGrandTotalAmounts();


        $materialArr   = [];
        
        foreach(MaterialItem::get() as $mi){
        
            $materialArr[ $mi->id ] = $mi;
        }

        $unit_options = Unit::toOptions();

        //$hash = generateComponentHash($project,$section,$component,$componentItems,$materialArr);

        if($request->header('X-STUDIO-MODE')){
            return view('project_studio/screen/component/display',[
                'project'               => $project,
                'section'               => $section,
                'contract_item'         => $contract_item,
                'component'             => $component,
                'componentItems'        => $componentItems,
                'materialItems'         => $materialItems,
                'unit_options'          => $unit_options,
                'back'                  => $back,
                'grand_total_amounts'   => $grand_total_amounts
            ]);
        }

        return view('component/display',[
            'project'               => $project,
            'section'               => $section,
            'contract_item'         => $contract_item,
            'component'             => $component,
            'componentItems'        => $componentItems,
            'materialItems'         => $materialItems,
            'unit_options'          => $unit_options,
            'back'                  => $back,
            'grand_total_amounts'   => $grand_total_amounts
        ]);
    }


    public function preview($id){

        $component = Component::findOrFail($id);

        if(!$this->hasAccess(['component:all:view'])){

            if( !$this->hasAccess(['component:own:view']) ){
                return view('access_denied');
            }

            if($component->created_by != $user->id){
                return view('access_denied');
            }
        }

        $contract_item   = $component->ContractItem;
        $section         = $component->section;
        $project         = $section->project;
        $componentItems  = $component->componentItems()->orderBy('id','ASC')->withCount('materialQuantities')->get();

        
        $materialItems   = [];
        
        foreach(MaterialItem::get() as $mi){
        
            $materialItems[ $mi->id ] = $mi;
        }
        
        $hash = generateComponentHash($project,$section,$component,$componentItems,$materialItems);
 
        $unit_options    = Unit::toOptions();

 

        $html = view('component/print',[
            'project'           => $project,
            'section'           => $section,
            'contract_item'     => $contract_item,
            'component'         => $component,
            'componentItems'    => $componentItems,
            'materialItems'     => $materialItems,
            'hash'              => $hash,
            'unit_options'      => $unit_options
        ])->render();


        $html2pdf = new Html2Pdf('P','A4','en', false, 'UTF-8', [5, 5, 15, 0]);
           

        try {
            $html2pdf->writeHTML($html);
            $html2pdf->output('Component - '.$component->name.' ['.str_pad($component->id,6,0,STR_PAD_LEFT ).'].pdf');
            $html2pdf->clean();
        
        }catch(Html2PdfException $e) {
            $html2pdf->clean();
        
            $formatter = new ExceptionFormatter($e);
            echo $html;
            echo $formatter->getHtmlMessage();

        
        } 

    }

    public function _update(Request $request){

        $id                  = (int) $request->input('id');
        $name                = $request->input('name') ?? '';
        $quantity            = $request->input('quantity');
        $status              = $request->input('status');
        $use_count           = (int) $request->input('use_count') ?? 1;
        $unit_id             = (int) $request->input('unit_id');
        $sum_flag            = (boolean) $request->input('sum_flag');

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

        $user = auth()->user();

        if(!$this->hasAccess(['component:all:update'])){

            if( !$this->hasAccess(['component:own:update']) ){
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Access Denied',
                    'data'      => []
                ]);
            }

            if($component->created_by != $user->id){
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Access Denied',
                    'data'      => []
                ]);
            }
        }

        $contract_item_id = $component->contract_item_id;
        
        $validator = Validator::make($request->all(),[
            'id'   => [
                'required',
                'integer',
                'gte:1'               
            ],
            'quantity' =>[
                'required',
                'numeric',
                'gte:1'
            ],
            'name' => [
                'required',
                'max:255',
                Rule::unique('components')->where(
                function ($query) use ($contract_item_id,$id,$name) {
                    return $query
                    ->where('contract_item_id', $contract_item_id)
                    ->where('name', $name)
                    ->where('id','!=',$id);
                }),
            ],
            'use_count' =>[
                'required',
                'integer',
                'gte:1'
            ],
            'unit_id'   => [
                'required',
                'integer',
                'gte:1'               
            ]
        ]);

        if ($validator->fails()) {
            
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $user_id    = $user->id;
        
        

        $component->name                         = $name;
        $component->quantity                     = $quantity;
        $component->use_count                    = $use_count;
        $component->status                       = 'PEND';
        $component->updated_by                   = $user_id;
        $component->unit_id                      = $unit_id;
        $component->sum_flag                     = $sum_flag;
        $component->save();

        $this->updateComponentItems($component, $user_id);

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id' => $id,
                'status' => $status
            ]
        ]);

    }

    private function updateComponentItems($component,$user_id){

        $component_items = ComponentItem::where('component_id',$component->id)->get();

        foreach($component_items as $item){

            if($item->function_type_id == 1){

                $this->updateItemAsFactor($item,$component,$user_id);

            }else if($item->function_type_id == 2){
               
                $this->updateItemAsDivisor($item,$component,$user_id);
                
            }else{
                $this->updateItemAsDirect($item,$user_id);
            }
        }
    }

    private function updateItemAsDivisor($item,$component,$user_id){

        $item_quantity = ($component->quantity / $item->function_variable ) / $component->use_count;

        $item->quantity     = ceil($item_quantity);

        $item->quantity     = $item_quantity;
        $item->updated_by   = $user_id;
        $item->save();
    }

    private function updateItemAsFactor($item,$component,$user_id){

        $item_quantity = ($component->quantity * $item->function_variable ) / $component->use_count;

        $item->quantity = ceil($item_quantity);

        $item->quantity     = $item_quantity;
        $item->updated_by   = $user_id;
        $item->save();
    }

    private function updateItemAsDirect($item,$user_id){
      
        $item->updated_by   = $user_id;
        $item->save();
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
 
         $component = Component::find($id);
         
         if(!$component){
             return response()->json([
                 'status'    => 0,
                 'message'   => 'Record not found',
                 'data'      => []
             ]);
         }
         
        $user = auth()->user();

        if(!$this->hasAccess(['component:all:delete'])){

            if( !$this->hasAccess(['component:own:delete']) ){
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Access Denied',
                    'data'      => []
                ]);
            }

            if($component->created_by != $user->id){
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Access Denied',
                    'data'      => []
                ]);
            }
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

        //$section_id         = (int) $request->input('section_id') ?? 0;
        $contract_item_id   = (int) $request->input('contract_item_id') ?? 0;
        $page               = (int) $request->input('page')     ?? 1;
        $limit              = (int) $request->input('limit')    ?? 0;
        $orderBy            = $request->input('order_by')       ?? 'id';
        $order              = $request->input('order')          ?? 'DESC';
        $query              = $request->input('query')          ?? '';
        $status             = $request->input('status')         ?? '';
        $result             = [];

        $component = new Component();

        // if($section_id){
        //     $component = $component->where('section_id',$section_id);
        // }

        if($contract_item_id){
            $component = $component->where('contract_item_id',$contract_item_id);
        }

        if($query != ''){
            $component = $component->where('name','LIKE','%'.$query.'%');
        }
        
        if($status != ''){
            $component = $component->where('status','=',$status);
        }

        //filter out deleted
        $component = $component->where('deleted_at','=',null);

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
