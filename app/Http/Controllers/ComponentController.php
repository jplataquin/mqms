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



class ComponentController extends Controller
{
    public function _create(Request $request){

        //todo check role

        $name               = $request->input('name') ?? '';
        $quantity           = $request->input('quantity') ?? 0;
        $contract_item_id   = (int) $request->input('contract_item_id');
        $section_id         = (int) $request->input('section_id');
        $use_count          = (int) $request->input('use_count') ?? 1;

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
            ]
        ]);
         
        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $contract_item = ContractItem::find($contract_item_id);

        if (!$contract_item) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Contract Item not found',
                'data'      => $validator->messages()
            ]);
        }

        $user_id = Auth::user()->id;

        $component = new Component();

        $component->name                   = $name;
        $component->contract_item_id       = $contract_item_id;
        $component->quantity               = $quantity;
        $component->unit_id                = $contract_item->unit_id;
        $component->use_count              = $use_count;
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

        $contract_item   = $component->ContractItem;
        $section         = $component->Section;
        $project         = $section->Project;
        
        $materialItems   = MaterialItem::orderBy('name','ASC')->get();
        $componentItems = $component->componentItems()->orderBy('id','ASC')->withCount('materialQuantities')->get();


        $materialArr   = [];
        
        foreach(MaterialItem::get() as $mi){
        
            $materialArr[ $mi->id ] = $mi;
        }

        $unit_options = Unit::toOptions();

        $hash = generateComponentHash($project,$section,$component,$componentItems,$materialArr);


        return view('component/display',[
            'project'           => $project,
            'section'           => $section,
            'contract_item'     => $contract_item,
            'component'         => $component,
            'componentItems'    => $componentItems,
            'materialItems'     => $materialItems,
            'hash'              => $hash,
            'unit_options'      => $unit_options
        ]);
    }

    public function preview($id){

        $component = Component::findOrFail($id);

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

        //todo check role

        $id                  = (int) $request->input('id');
        $name                = $request->input('name') ?? '';
        $quantity            = $request->input('quantity');
        $status              = $request->input('status');
        $use_count           = (int) $request->input('use_count') ?? 1;

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
                function ($query) use ($section_id,$id,$name) {
                    return $query
                    ->where('section_id', $section_id)
                    ->where('name', $name)
                    ->where('id','!=',$id);
                }),
            ],
            'use_count' =>[
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
        $component->quantity                     = $quantity;
        $component->use_count                    = $use_count;
        $component->status                       = 'PEND';
        $component->updated_by                   = $user_id;
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

        $item->quantity = ceil($item_quantity);

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
        $status     = $request->input('status')         ?? '';
        $result     = [];

        $component = new Component();

        $component = $component->where('section_id',$section_id);

        if($query != ''){
            $component = $component->where('name','LIKE','%'.$query.'%');
        }
        
        if($status != ''){
            $component = $component->where('status','=',$status);
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
