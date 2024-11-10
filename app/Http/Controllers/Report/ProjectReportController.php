<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Project;
use App\Models\Section;
use App\Models\Component;
use App\Models\ContractItem;
use App\Models\Supplier;
use App\Models\User;
use App\Models\MaterialQuantityRequestItem;
use App\Models\MaterialQuantityRequest;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrder;
use App\Models\MaterialItem;
use App\Models\MaterialGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class ProjectReportController extends Controller {
    
    public function parameters(){

        $projects = Project::where('deleted_at',null)->get();

        $material_groups = MaterialGroup::where('deleted_at',null)->get();

        $users = User::where('deleted_at',null)->get();

        return view('report/project/parameters',[
            'projects'          => $projects
        ]);
    }

    public function generate(Request $request){

        $url = htmlspecialchars( str_replace(url('').'/','',$request->fullUrl()) );
       
        $project_id             = $request->input('project_id');
        $section_id             = $request->input('section_id');
        $contract_item_id       = (int) $request->input('contract_item_id');
        $component_id           = (int) $request->input('component_id');
        $as_of                  = $request->input('as_of');
        $material_item_id_arr   = explode(',',$request->input('material_items'));

        $project_name       = '';
        $section_name       = '';
        $contract_item_name = '*';
        $component_name     = '*';
        $as_of_display      = '*';

        $validator = Validator::make($request->all(),[
            'project_id' =>[
                'required',
                'integer',
                'gte:1'
            ],
            'section_id' =>[
                'required',
                'integer',
                'gte:1'
            ],
            'contract_item_id' =>[
                'nullable',
                'integer',
                'gte:1'
            ],
            'component_id' =>[
                'nullable',
                'integer',
                'gte:1'
            ],
            'as_of' => [
                'nullable',
                'date_format:Y-m-d'
            ]
        ]);

        if ($validator->fails()) {
            
            return view('/report/project/error',[
                'message'          => '',
                'validation_error' => $validator->messages()
            ]);
        }


        $project        = Project::findOrFail($project_id);
        $project_name   = $project->name;

        //Get section that belongs to the project and is not deleted
        $section        = Section::where('project_id',$project_id)->where('id',$section_id)->where('deleted_at',null)->first();
        $section_name   = $section->name;

        if(!$section){
            return view('/report/project/error',[
                'message'          => 'Section record not found',
                'validation_error' => []
            ]);
        }

        if($contract_item_id){
            
            $contract_item = ContractItem::where('id',$contract_item_id)
            ->where('section_id',$section->id)
            ->where('deleted_at',null)
            ->first();

            if(!$contract_item){
                return view('/report/project/error',[
                    'message'          => 'Contract Item record not found',
                    'validation_error' => []
                ]); 
            }

            $contract_item_name = $contract_item->name();
        }

        if($component_id){

            $component = ContractItem::where('id',$component_id)
            ->where('section_id',$project->id)
            ->where('deleted_at',null)
            ->first();

            if(!$component){
                return view('/report/project/error',[
                    'message'          => 'Component record not found',
                    'validation_error' => []
                ]); 
            }

            $component_name = $component->name;
        }

        $report                                 = [];
        $contract_item_arr                      = [];
        $component_arr                          = [];
        $component_item_arr                     = [];
        $material_quantity_arr                  = [];
        $material_item_arr                      = [];
        $total_po_overhead_arr                  = [];
        $valid_po_ids                           = [];
        $valid_material_quantity_request_ids    = [];

        $contract_items = ContractItem::where('section_id',$section_id)->where('deleted_at',null)->orderBy('item_code','ASC');

        if($contract_item_id){
            $contract_items = $contract_items->where('id',$contract_item_id);
        }

        $contract_items = $contract_items->get();


        foreach($contract_items as $contract_item){

            if( !isset( $report[ $contract_item->id ] ) ){
                $report[ $contract_item->id ]               = [];
                $contract_item_arr[ $contract_item->id ]    = $contract_item;
            }

            $components = $contract_item->Components()
            ->where('deleted_at',null)->orderBy('name','ASC');

            if($component_id){

                $components = $components->where('id',$component_id);
            }

            $components = $components->get();


            foreach($components as $component){

                if( !isset( $report[ $contract_item->id ][ $component->id ] ) ){
                    $report[ $contract_item->id ][ $component->id ] = [];
                }


                $purchase_orders = PurchaseOrder::where('component_id',$component->id)
                ->where('status','APRV')
                ->where('deleted_at',null)
                ->where('project_id', $project_id)
                ->where('section_id',$section_id);
                
                //Date filter for puchase order
                if($as_of){
                    $purchase_orders = $purchase_orders->where('approved_at','<=',$as_of.' 23:59:59');
                }
                
                $purchase_orders = $purchase_orders->get();

                $total_po_overhead = 0;
                
                foreach($purchase_orders as $purchase_order){
                    
                    //Add to valid po id
                    $valid_po_ids[] = $purchase_order->id;
                    
                    //Calculate total po overhead
                    try{

                        $extras = json_decode($purchase_order->extras);
                        
                        foreach($extras as $extra){
                            $total_po_overhead = $total_po_overhead + (float) $extra->value;
                        }

                    }catch(\Exception $e){
                        //do nothing
                    }
                }

                $total_po_overhead_arr[ $component->id ] = $total_po_overhead;

                $material_quantity_requests = MaterialQuantityRequest::where('component_id',$component->id)
                ->where('status','APRV')
                ->where('deleted_at',null)
                ->where('project_id', $project_id)
                ->where('section_id',$section_id);
                
                //Date filter for material quantitiy requests
                if($as_of){
                    $material_quantity_requests = $material_quantity_requests->where('approved_at','<=',$as_of.' 23:59:59');
                }

                $material_quantity_requests = $material_quantity_requests->get();

                foreach($material_quantity_requests as $material_quantity_request){
                    //Add valid material quantity requests id
                    $valid_material_quantity_request_ids[] = $material_quantity_request->id;
                }

                $component_arr[ $component->id ] = $component;

                $component_items= $component->ComponentItems()->orderBy('name','ASC')->where('deleted_at',null)->get();

                foreach($component_items as $component_item){

                    if( !isset( $report[ $contract_item->id ][ $component->id ][ $component_item->id ] ) ){
                        $report[ $contract_item->id ][ $component->id ][ $component_item->id ]  = [];
                        $component_item_arr[ $component_item->id ]                              = $component_item;
                    }

                    $material_quantities = $component_item->MaterialQuantities()->where('deleted_at',null)->get();

                    foreach($material_quantities as $material_quantity){

                        if( !isset( $report[ $contract_item->id ][ $component->id ][ $component_item->id ][ $material_quantity->id ] ) ){
                            $report[ $contract_item->id ][ $component->id ][ $component_item->id ][ $material_quantity->id ]    = [];
                            $material_quantity_arr[ $material_quantity->id ] = $material_quantity;
                  
                        }

                        
                        $material_item_arr[ $material_quantity->material_item_id ] = MaterialItem::find($material_quantity->material_item_id);

                        //Get total request quantity
                        $total_requested_quantity = MaterialQuantityRequestItem::where('component_item_id',$component_item->id)
                        ->where('material_item_id', $material_quantity->material_item_id)
                        ->where('status','APRV')
                        ->whereIn('material_quantity_request_id',$valid_material_quantity_request_ids)
                        ->sum('requested_quantity');

                        $total_po_quantity = PurchaseOrderItem::where('component_item_id',$component_item->id)
                        ->where('material_item_id',$material_quantity->material_item_id)
                        ->where('status','APRV')
                        ->whereIn('purchase_order_id',$valid_po_ids)
                        ->sum('quantity');


                        $total_po_amount = PurchaseOrderItem::where('component_item_id',$component_item->id)
                        ->where('material_item_id',$material_quantity->material_item_id)
                        ->where('status','APRV')
                        ->whereIn('purchase_order_id',$valid_po_ids)
                        ->select( DB::raw('SUM(quantity * price) as total') )
                        ->first();

                        $report[ $contract_item->id ][ $component->id ][ $component_item->id ][ $material_quantity->id ]['budget_quantity']  = $material_quantity->quantity;
                        $report[ $contract_item->id ][ $component->id ][ $component_item->id ][ $material_quantity->id ]['request_quantity'] = $total_requested_quantity;
                        $report[ $contract_item->id ][ $component->id ][ $component_item->id ][ $material_quantity->id ]['po_quantity']      = $total_po_quantity;
                        $report[ $contract_item->id ][ $component->id ][ $component_item->id ][ $material_quantity->id ]['po_amount']        = $total_po_amount->total;
                        
                    }//material quantity

                }//component item

            }//component

        }//contract item


        return view('/report/project/generate',[
            'project_name'          => $project_name,
            'section_name'          => $section_name,
            'contract_item_name'    => $contract_item_name,
            'component_name'        => $component_name,
            'contract_item_arr'     => $contract_item_arr,
            'component_arr'         => $component_arr,
            'component_item_arr'    => $component_item_arr,
            'material_quantity_arr' => $material_quantity_arr,
            'material_item_arr'     => $material_item_arr,
            'total_po_overhead_arr' => $total_po_overhead_arr,
            'report'                => $report,
            'as_of'                 => $as_of,
            'url'                   => $url
        ]);
    }

}
