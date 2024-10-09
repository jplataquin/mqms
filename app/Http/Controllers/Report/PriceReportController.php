<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\MaterialQuantityRequestItem;
use App\Models\MaterialQuantityRequest;
use App\Models\MaterialItem;
use App\Models\MaterialCanvass;
use App\Models\MaterialGroup;
use App\Models\PaymentTerm;
use App\Models\Project;
use App\Models\Section;
use App\Models\ContractItem;
use App\Models\Component;
use Illuminate\Support\Facades\Validator;
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;
use Carbon\Carbon;

class PriceReportController extends Controller
{

    public function parameters(){

        $projects = Project::where('deleted_at',null)->get();

        $material_groups = MaterialGroup::where('deleted_at',null)->get();

        return view('/report/price/parameters',[
            'projects'          => $projects,
            'material_groups'   => $material_groups
        ]);
    }

    public function generate(Request $request){
        
        $project_id             = $request->input('project_id');
        $section_id             = $request->input('section_id');
        $contract_item_id       = $request->input('contract_item_id');
        $component_id           = $request->input('component_id');
        $from                   = $request->input('from');
        $to                     = $request->input('to');
        $material_group_id      = (int) $request->input('material_group_id');
        $material_item_id_arr   = explode(',',$request->input('material_items'));

        $validator = Validator::make($request->all(),[
            'material_group_id'   => [
                'required',
                'integer',
                'gte:1'               
            ],
            'from' => [
                'date_format:Y-m-d'
            ],
            'to' => [
                'date_format:Y-m-d'
            ]
        ]);

        if ($validator->fails()) {
            
            return view('/report/price/error',[
                'message'          => '',
                'validation_error' => $validator->messages()
            ]);
        }
        
        $project_name       = '';
        $section_name       = '';
        $contract_item_name = '';
        $component_name     = '';

        $material_group = MaterialGroup::findOrFail($material_group_id);



        //Query material request
        $material_request = new MaterialQuantityRequest();

        if($project_id){

            $project_id = (int) $project_id;
            $material_request = $material_request->where('project_id',$project_id);

            $project        = Project::find($project_id);
            $project_name   = $project->name;

            if($section_id){
                $section_id = (int) $section_id;
                $material_request = $material_request->where('section_id',$section_id);

                $section        = Section::find($section_id);
                $section_name   = $section->name;

                if($contract_item_id){
                    $contract_item_id = (int) $contract_item_id;
                    $material_request = $material_request->where('contract_item_id',$contract_item_id);
                    
                    $contract_item = ContractItem::find($contract_item_id);
                    $contract_item_name = $contract_item->item_code.' '.$contract_item->description;

                    if($component_id){
                        $component_id = (int) $component_id;
                        $material_request = $material_request->where('component_id',$component_id);
                        
                    }            
                }   
            }
        }/*****/
  
  
        $material_request = $material_request->where('status','APRV');

        $material_request_result = $material_request->get();

        $material_request_id_arr = [];
        
        foreach($material_request_result as $row){
            $material_request_id_arr[] = $row->id;
        }


        $material_request_items = MaterialQuantityRequestItem::where('status','APRV')
        ->orderBy('created_at','DESC');
        
        if($material_request_id_arr){
            $material_request_items = $material_request_items->whereIn('material_quantity_request_id',$material_request_id_arr);
        }
        
        if($material_item_id_arr){
            $material_request_items = $material_request_items->whereIn('material_item_id',$material_item_id_arr);
        }else{

            //Get material_items if array is empty
            $material_item_id_arr = [];
    
            foreach($material_group->Items as $row){
                $material_item_id_arr[] = $row->id;
            }

            $material_request_items = $material_request_items->whereIn('material_item_id',$material_item_id_arr);
        }

        $material_request_items = $material_request_items->get();

        $result = [];

        foreach($material_request_items as $mr_item){

            $mc = MaterialCanvass::where('material_quantity_request_item_id',$mr_item->id)
            ->where('status','APRV');

            if($from){
                $mc = $mc->where('created_at','>=',$from);
            }
      
            if($to){
                $mc = $mc->where('created_at','<=',$to);
            }

            $mc = $mc->first();

            if(!$mc) continue;

            //Set material_item_id grouping
            if(!isset($result[$mr_item->material_item_id])){
                $result[$mr_item->material_item_id] = [];
            }

            //Set supplier_id grouping
            if(! isset( $result[$mr_item->material_item_id][$mc->supplier_id] ) ){
                $result[$mr_item->material_item_id][$mc->supplier_id] = [];
            }

            //Set payment_term_id grouping
            if(! isset($result[$mr_item->material_item_id][$mc->supplier_id][$mc->payment_term_id]) ){
                $result[$mr_item->material_item_id][$mc->supplier_id][$mc->payment_term_id] = [];
            }

            //Set price grouping
            if(! isset( $result[$mr_item->material_item_id][$mc->supplier_id][$mc->payment_term_id][$mc->price] ) ){
                $result[$mr_item->material_item_id][$mc->supplier_id][$mc->payment_term_id][$mc->price] = $mc->created_at;
            }

            $result[$mr_item->material_item_id][$mc->supplier_id][$mc->payment_term_id][$mc->price] = $mc->created_at;
        }

        $supplier_options       = Supplier::toOptions();
        $payment_term_options   = PaymentTerm::toOptions();
        $material_item_options  = MaterialItem::toOptions();

        return view('/report/price/generate',[
            'result'                => $result,
            'project_name'          => $project_name,
            'section_name'          => $section_name,
            'contract_item_name'    => $contract_item_name,
            'component_name'        => $component_name,
            'from'                  => $from,
            'to'                    => $to,
            'material_group'        => $material_group,
            'supplier_options'      => $supplier_options,
            'payment_term_options'  => $payment_term_options,
            'material_item_options' => $material_item_options
        ]);

    }


    public function print(Request $request){
        
        $project_id             = $request->input('project_id');
        $section_id             = $request->input('section_id');
        $contract_item_id       = $request->input('contract_item_id');
        $component_id           = $request->input('component_id');
        $from                   = $request->input('from');
        $to                     = $request->input('to');
        $material_group_id      = (int) $request->input('material_group_id');
        $material_item_id_arr   = explode(',',$request->input('material_items'));

        $validator = Validator::make($request->all(),[
            'material_group_id'   => [
                'required',
                'integer',
                'gte:1'               
            ],
            'from' => [
                'date_format:Y-m-d'
            ],
            'to' => [
                'date_format:Y-m-d'
            ]
        ]);

        if ($validator->fails()) {
            
            return view('/report/price/error',[
                'message'          => '',
                'validation_error' => $validator->messages()
            ]);
        }
        
        $project_name       = '';
        $section_name       = '';
        $contract_item_name = '';
        $component_name     = '';

        $material_group = MaterialGroup::findOrFail($material_group_id);



        //Query material request
        $material_request = new MaterialQuantityRequest();

        if($project_id){

            $project_id = (int) $project_id;
            $material_request = $material_request->where('project_id',$project_id);

            $project        = Project::find($project_id);
            $project_name   = $project->name;

            if($section_id){
                $section_id = (int) $section_id;
                $material_request = $material_request->where('section_id',$section_id);

                $section        = Section::find($section_id);
                $section_name   = $section->name;

                if($contract_item_id){
                    $contract_item_id = (int) $contract_item_id;
                    $material_request = $material_request->where('contract_item_id',$contract_item_id);
                    
                    $contract_item = ContractItem::find($contract_item_id);
                    $contract_item_name = $contract_item->item_code.' '.$contract_item->description;

                    if($component_id){
                        $component_id = (int) $component_id;
                        $material_request = $material_request->where('component_id',$component_id);
                        
                    }            
                }   
            }
        }/*****/
  
  
        $material_request = $material_request->where('status','APRV');

        $material_request_result = $material_request->get();

        $material_request_id_arr = [];
        
        foreach($material_request_result as $row){
            $material_request_id_arr[] = $row->id;
        }


        $material_request_items = MaterialQuantityRequestItem::where('status','APRV')
        ->orderBy('created_at','DESC');
        
        if($material_request_id_arr){
            $material_request_items = $material_request_items->whereIn('material_quantity_request_id',$material_request_id_arr);
        }
        
        if($material_item_id_arr){
            $material_request_items = $material_request_items->whereIn('material_item_id',$material_item_id_arr);
        }else{

            //Get material_items if array is empty
            $material_item_id_arr = [];
    
            foreach($material_group->Items as $row){
                $material_item_id_arr[] = $row->id;
            }

            $material_request_items = $material_request_items->whereIn('material_item_id',$material_item_id_arr);
        }

        $material_request_items = $material_request_items->get();

        $result = [];

        foreach($material_request_items as $mr_item){

            $mc = MaterialCanvass::where('material_quantity_request_item_id',$mr_item->id)
            ->where('status','APRV');

            if($from){
                $mc = $mc->where('created_at','>=',$from);
            }
      
            if($to){
                $mc = $mc->where('created_at','<=',$to);
            }

            $mc = $mc->first();

            if(!$mc) continue;

            //Set material_item_id grouping
            if(!isset($result[$mr_item->material_item_id])){
                $result[$mr_item->material_item_id] = [];
            }

            //Set supplier_id grouping
            if(! isset( $result[$mr_item->material_item_id][$mc->supplier_id] ) ){
                $result[$mr_item->material_item_id][$mc->supplier_id] = [];
            }

            //Set payment_term_id grouping
            if(! isset($result[$mr_item->material_item_id][$mc->supplier_id][$mc->payment_term_id]) ){
                $result[$mr_item->material_item_id][$mc->supplier_id][$mc->payment_term_id] = [];
            }

            //Set price grouping
            if(! isset( $result[$mr_item->material_item_id][$mc->supplier_id][$mc->payment_term_id][$mc->price] ) ){
                $result[$mr_item->material_item_id][$mc->supplier_id][$mc->payment_term_id][$mc->price] = $mc->created_at;
            }

            $result[$mr_item->material_item_id][$mc->supplier_id][$mc->payment_term_id][$mc->price] = $mc->created_at;
        }

        $supplier_options       = Supplier::toOptions();
        $payment_term_options   = PaymentTerm::toOptions();
        $material_item_options  = MaterialItem::toOptions();

        $current_datetime = Carbon::now();

        return view('/report/price/generate',[
            'result'                => $result,
            'project_name'          => $project_name,
            'section_name'          => $section_name,
            'contract_item_name'    => $contract_item_name,
            'component_name'        => $component_name,
            'from'                  => $from,
            'to'                    => $to,
            'material_group'        => $material_group,
            'supplier_options'      => $supplier_options,
            'payment_term_options'  => $payment_term_options,
            'material_item_options' => $material_item_options,
            'current_datetime'      => $current_datetime
        ]);

    }
}