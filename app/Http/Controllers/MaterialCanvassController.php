<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Section;
use App\Models\Component;
use App\Models\ComponentItem;
use App\Models\MaterialQuantityRequest;
use App\Models\MaterialQuantityRequestItem;
use App\Models\MaterialQuantity;
use App\Models\MaterialCanvass;
use App\Models\ContractItem;
use App\Models\PaymentTerm;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;
use Illuminate\Support\Str;

class MaterialCanvassController extends Controller
{
    public function list(){

        $projects = Project::orderBy('name','ASC')->where('status','=','ACTV')->get();

        
        return view('material_canvass/list',[
            'projects' => $projects
        ]);
    }

 
 
    public function _list(Request $request){

        //todo check role

        $page       = (int) $request->input('page')     ?? 1;
        $limit      = (int) $request->input('limit')    ?? 10;
        $project_id = (int) $request->input('project_id')  ?? 0;
        $section_id      = (int) $request->input('section_id')  ?? 0;
        $component_id    = (int) $request->input('component_id')  ?? 0;
        $query      = (int) $request->input('query')    ?? 0;
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $result = [];

        $materialQuantityRequest = new MaterialQuantityRequest();
        $user_id = Auth::user()->id;

        $materialQuantityRequest = $materialQuantityRequest->where('status','=','APRV');
        
        //$materialQuantityRequest = $materialQuantityRequest->where('created_by','=',$user_id);
       
        if($query){
            $materialQuantityRequest = $materialQuantityRequest->where('id','=',$query);
        }

        if($project_id){
            
            $materialQuantityRequest = $materialQuantityRequest->where('project_id','=',$project_id);

            if($section_id){
                $materialQuantityRequest = $materialQuantityRequest->where('section_id','=',$section_id);

                if($component_id){
                    $materialQuantityRequest = $materialQuantityRequest->where('component_id','=',$component_id);

                }
            }
        }

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $materialQuantityRequest->orderBy($orderBy,$order)->skip($page)->take($limit)->with('Project')->with('Section')->with('Component')->with('User')->get();
            
        }else{

            $result = $materialQuantityRequest->orderBy($orderBy,$order)->take($limit)->with('Project')->with('Section')->with('Component')->with('User')->get();
        }

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> $result
        ]);
    }
    
    public function display($id){
        
        $materialQuantityRequest = MaterialQuantityRequest::findOrFail($id);

        if($materialQuantityRequest->status != 'APRV'){
            return show404();
        }

        $project                = $materialQuantityRequest->Project;
        $section                = $materialQuantityRequest->Section;
        $contract_item          = $materialQuantityRequest->ContractItem;
        $component              = $materialQuantityRequest->Component;
        $items                  = $materialQuantityRequest->Items()->with('MaterialCanvass')->get();
       
        $component_item_id  = [];

        foreach($items as $item){
            $component_item_id[]    = $item->component_item_id; 
            $material_quantity_id[] = $item->material_quantity_id;
            $material_item_id[]     = $item->material_item_id;
        }

        $suppliers              = Supplier::orderBy('name','ASC')->get();
        $component_items        = ComponentItem::whereIn('id',$component_item_id)->get();
        $material_quantities    = MaterialQuantity::whereIn('id',$material_quantity_id)->get();

        $material_items = DB::table('material_items')->whereIn('id',$material_item_id)->get();

        $component_item_arr = [];

        //Arrange component item by id
        foreach($component_items as $ci){
            $component_item_arr[$ci->id] = $ci;
        }

        
        $material_item_arr = [];

        //Arrange material item by id
        foreach($material_items as $mi){
            $material_item_arr[$mi->id] = $mi;
        }

        $payment_terms = PaymentTerm::toOptions();

        return view('material_canvass/display',[
            'material_quantity_request' => $materialQuantityRequest,
            'project'                   => $project,
            'section'                   => $section,
            'component'                 => $component,
            'items'                     => $items,
            'component_item_arr'        => $component_item_arr,
            'material_item_arr'         => $material_item_arr,
            'suppliers'                 => $suppliers,
            'payment_terms'             => $payment_terms,
            'contract_item'             => $contract_item
        ]);
    }

    public function print($id){
        
        $materialQuantityRequest = MaterialQuantityRequest::findOrFail($id);

        if($materialQuantityRequest->status != 'APRV'){
            return show404();
        }

        $project                = $materialQuantityRequest->Project;
        $section                = $materialQuantityRequest->Section;
        $component              = $materialQuantityRequest->Component;
        $contract_item          = $materialQuantityRequest->ContractItem;
        $items                  = $materialQuantityRequest->Items()->with('MaterialCanvass')->get();
       
        $component_item_id  = [];

        foreach($items as $item){
            $component_item_id[]    = $item->component_item_id; 
            $material_quantity_id[] = $item->material_quantity_id;
            $material_item_id[]     = $item->material_item_id;
        }

        $suppliers              = Supplier::orderBy('name','ASC')->get();
        $component_items        = ComponentItem::whereIn('id',$component_item_id)->get();
        $material_quantities    = MaterialQuantity::whereIn('id',$material_quantity_id)->get();

        $material_items = DB::table('material_items')->whereIn('id',$material_item_id)->get();

        $component_item_arr = [];

        //Arrange component item by id
        foreach($component_items as $ci){
            $component_item_arr[$ci->id] = $ci;
        }

        
        $material_item_arr = [];

        //Arrange material item by id
        foreach($material_items as $mi){
            $material_item_arr[$mi->id] = $mi;
        }

        $payment_terms = PaymentTerm::toOptions();

        $payment_term_arr = [];

        foreach($payment_terms as $payment_term){

            $payment_term_arr[ $payment_term->id ] = $payment_term;
        }

        $supplier_arr = [];

        foreach($suppliers as $supplier){
            $supplier_arr[$supplier->id] = $supplier;
        }

        $html = view('material_canvass/print',[
            'material_quantity_request' => $materialQuantityRequest,
            'project'                   => $project,
            'section'                   => $section,
            'contract_item'             => $contract_item,
            'component'                 => $component,
            'items'                     => $items,
            'component_item_arr'        => $component_item_arr,
            'material_item_arr'         => $material_item_arr,
            'supplier_arr'              => $supplier_arr,
            'payment_term_arr'          => $payment_term_arr,
            'current_datetime'          => Carbon::now()
        ]);exit;

        $html2pdf = new Html2Pdf('P','A4','en', false, 'UTF-8', [5, 5, 10, 0]);
           

        try {
            $html2pdf->writeHTML($html);
            $html2pdf->output('Material Canvass - '.str_pad($materialQuantityRequest->id,0,6,STR_PAD_LEFT ).'.pdf');
            $html2pdf->clean();
        
        }catch(Html2PdfException $e) {
            $html2pdf->clean();
        
            $formatter = new ExceptionFormatter($e);
            echo $html;
            echo $formatter->getHtmlMessage();        
        } 
       
    }

    public function _create(Request $request){
        
        $material_quantity_request_id = (int) $request->input('material_quantity_request_id');

        
        try{
            $data = json_decode($request->input('canvassItems'),true);

            if(is_null($data)){
                throw('Error');
            }

        } catch (\Exception $e) {

            return response()->json([
                'status'    => 0,
                'message'   => $e->getMessage(),
                'data'      => []
            ]);
        }

        $validator1 = Validator::make(['canvassItems'=>$data],[
            'canvassItems.*.material_quantity_request_item_id' => [
                'required',
                'integer'
            ],
            'canvassItems.*.supplier_id' => [
                'required',
                'integer'
            ],
            'canvassItems.*.payment_term_id' => [
                'required',
                'integer'
            ],
            'canvassItems.*.price' => [
                'required',
                'decimal:2'
            ]
        ]);

        if ($validator1->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator1->messages()
            ]);
        }

        //Make sure no duplicate canvass
        $duplicateArr = [];

        DB::enableQueryLog();

        foreach($data as $d){
            
            $testA = MaterialCanvass::where('supplier_id','=',$d['supplier_id'])
            ->where('payment_term_id','=',$d['payment_term_id'])
            ->where('material_quantity_request_item_id','=',$d['material_quantity_request_item_id'])
            ->where(function($q){
                return $q->where('status','!=','VOID')->Where('status','!=','REJC');
            })->exists();

            if($testA){
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Error: Duplicate canvass on the an item (1)',
                    'data'      => DB::getQueryLog()
                ]);
            }

            $testB = $d['supplier_id'].'-'.$d['payment_term_id'].'-'.$d['material_quantity_request_item_id'];

            if(in_array($testB,$duplicateArr)){
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Error: Duplicate canvass on the an item (2)',
                    'data'      => []
                ]);
            }

            $duplicateArr[] = $testB;
        }


        $validator2 = Validator::make($request->all(),[
            'material_quantity_request_id' => [
                'required',
                'integer'
            ]
        ]);

        if ($validator2->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator2->messages()
            ]);
        }

        $materialQuantityRequest = MaterialQuantityRequest::find($material_quantity_request_id);
        
        if(!$materialQuantityRequest){
            return response()->json([
                'status'    => 0,
                'message'   => 'Material Quantity Request record not found',
                'data'      => []
            ]);
        }

        if($materialQuantityRequest->status != 'APRV'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Material Quantity Request has not yet been approved',
                'data'      => []
            ]);
        }


        $prepData = [];

        $user_id = Auth::user()->id;

        foreach($data as $d){
            $prepData[] = [
                'material_quantity_request_id'      => $material_quantity_request_id,
                'material_quantity_request_item_id' => $d['material_quantity_request_item_id'],
                'supplier_id'                       => $d['supplier_id'],
                'payment_term_id'                   => $d['payment_term_id'],
                'status'                            => 'PEND',
                'price'                             => $d['price'],
                'created_by'                        => $user_id,
                'created_at'                        => Carbon::now()
            ];
        }

        MaterialCanvass::insert($prepData);

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id' => $material_quantity_request_id
            ]
        ]);
    }


    public function _delete(Request $request){

        $id = (int) $request->input('id');

        $materialCanvass = MaterialCanvass::find($id);

        if(!$materialCanvass){

            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }

        if($materialCanvass->status == 'APRV' || $materialCanvass->status == 'VOID'){
            return repsponse()->json([
                'status'    => 0,
                'message'   => 'Record cannot be delete, status is already approved',
                'data'      => []
            ]);
        }

        $user_id = Auth::user()->id;

        $materialCanvass->forceDelete();

        return response()->json([
            'status' => 1,
            'message' => '',
            'data' => []
        ]);
    }

    public function _void(Request $request){

        $id = (int) $request->input('id');

        $materialCanvass = MaterialCanvass::find($id);

        if(!$materialCanvass){

            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }

        if($materialCanvass->status != 'APRV'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record cannot be void, status is not approved',
                'data'      => []
            ]);
        }

        $user_id = Auth::user()->id;

        $materialCanvass->void_by = $user_id;
        $materialCanvass->status  = 'VOID';
        $materialCanvass->void_at = Carbon::now();
        
        $materialCanvass->save();
        
        return response()->json([
            'status' => 1,
            'message' => '',
            'data' => []
        ]);
    }
   
  
}
