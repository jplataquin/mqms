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
use App\Models\MaterialItem;
use App\Models\MaterialGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProjectReportController extends Controller {
    
    public function generate(Request $request){

        $project_id             = $request->input('project_id');
        $section_id             = $request->input('section_id');
        $contract_item_id       = $request->input('contract_item_id');
        $component_id           = $request->input('component_id');
        $from                   = $request->input('from');
        $to                     = $request->input('to');
        $requested_by           = $request->input('requested_by');
        $status                 = $request->input('status');
        $material_item_id_arr   = explode(',',$request->input('material_items'));

        $project_name       = '';
        $section_name       = '';

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
            ]
        ]);

        if ($validator->fails()) {
            
            return view('/report/material_request/error',[
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
            return view('/report/material_request/error',[
                'message'          => 'Section record not found',
                'validation_error' => []
            ]);
        }

        $report                 = [];
        $contract_item_arr      = [];
        $component_arr          = [];
        $component_item_arr     = [];
        $material_quantity_arr  = [];
        $material_item_arr      = [];

        $contract_items = ContractItem::where('section_id',$section_id)->where('deleted_at',null)->get();

        foreach($contract_items as $contract_item){

            if( !isset( $report[ $contract_item->id ] ) ){
                $report[ $contract_item->id ]               = [];
                $contract_item_arr[ $contract_item->id ]    = $contract_item;
            }

            $components = $contract_item->Components()->where('deleted_at',null)->get();

            foreach($components as $component){

                if( !isset( $report[ $contract_item->id ][ $component->id ] ) ){
                    $report[ $contract_item->id ][ $component->id ] = [];
                    $component_arr[ $component->id ]                = $component;
                }

                $component_items= $component->ComponentItems()->where('deleted_at',null)->get();

                foreach($component_items as $component_item){

                    if( !isset( $report[ $contract_item->id ][ $component->id ][ $component_item->id ] ) ){
                        $report[ $contract_item->id ][ $component->id ][ $component_item->id ]  = [];
                        $component_item_arr[ $component_item->id ]                              = $component_item;
                    }

                    $material_quantities = $component_item->MaterialQuantities()->where('deleted_at',null);

                    foreach($material_quantities as $material_quantity){

                        if( !isset( $report[ $contract_item->id ][ $component->id ][ $component_item->id ][ $material_quantity->id ] ) ){
                            $report[ $contract_item->id ][ $component->id ][ $component_item->id ][ $material_quantity->id ]    = [];
                            $material_quantity[ $material_quantity->id ]                                                        = $material_quantity;
                        }

                        $material_item_arr[ $material_quantity->material_item_id ] = MaterialItem::find($material_quantity->material_item_id);

                        //Get total request quantity
                        $total_requested_quantity = MaterialQuantityRequestItem::where('component_item_id',$component_item->id)
                        ->where('material_item_id', $material_quantity->material_item_id)
                        ->where('status','APRV')
                        ->sum('requested_quantity');

                        $total_po_quantity = PurchaseOrderItem::where('component_item_id',$component_item->id)
                        ->where('material_item_id',$material_quantity->material_item_id)
                        ->where('status','APRV')
                        ->sum('quantity');

                        $report[ $contract_item->id ][ $component->id ][ $component_item->id ][ $material_quantity->id ]['budget_quantity']  = $material_quantity->quantity;
                        $report[ $contract_item->id ][ $component->id ][ $component_item->id ][ $material_quantity->id ]['request_quantity'] = $total_requested_quantity;
                        $report[ $contract_item->id ][ $component->id ][ $component_item->id ][ $material_quantity->id ]['po_quantity']      = $total_po_quantity;
                        
                    }//material quantity

                }//component item

            }//component

        }//contract item


        return view('/report/material_request/generate',[
            'project_name'          => $project_name,
            'section_name'          => $section_name,
            'contract_item_arr'     => $contract_item_arr,
            'component_arr'         => $component_arr,
            'component_item_arr'    => $component_item_arr,
            'material_quantity_arr' => $material_quantity_arr,
            'material_item_arr'     => $material_item_arr,
            'report'                => $report,
            'from'                  => $from,
            'to'                    => $to
        ]);
    }

}
