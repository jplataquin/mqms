<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Project;
use App\Models\Section;
use App\Models\Component;
use App\Models\Supplier;
use App\Models\MaterialQuantityRequestItem;
use App\Models\MaterialQuantity;
use App\Models\PurchaseOrderItem;
use App\Models\MaterialItem;
use Illuminate\Support\Facades\DB;

class ReportAController extends Controller
{
    public function select(){

        $projects = Project::orderBy('name','ASC')->get();

        return view('reports/report_a/select',[
            'projects' => $projects
        ]);
    }

    public function generate(Request $request,$project_id, $section_id, $component_id){

        $project_id     = (int) $project_id;
        $section_id     = (int) $section_id;
        $component_id   = (int) $component_id;

        $project = Project::findOrFail($project_id);
                   
        $section = Section::where('project_id',$project_id)->where('id',$section_id)->first();

        if(!$section){
            return abort(404);
        }

        $component = Component::where('section_id',$section_id)->where('id',$component_id)->first();
        
        if(!$component){
            return abort(404);
        }
        
        $component_items = $component->ComponentItems;

        $material_quantity_requet_item  = [];
        $material_quantity              = [];
        $purchase_order_item            = [];

        foreach($component_items as $component_item){
            $material_quantity_request_item[$component_item->id] = MaterialQuantityRequestItem::where('component_item_id',$component_item->id)
                                    ->where('status','APRV')
                                    ->select(DB::raw('SUM(requested_quantity) AS total, material_item_id'))
                                    ->groupBy('material_item_id')
                                    ->get();

            $material_quantity[$component_item->id] = MaterialQuantity::where('component_item_id',$component_item->id)->get();
            
            $purchase_order_item[$component_item->id] = PurchaseOrderItem::where('status','APRV')
                ->where('component_item_id',$component_item->id)
                ->groupBy('material_item_id')
                ->select(DB::raw('SUM(quantity) AS total_quantity, SUM(price) AS total_price, material_item_id'))
                ->get();
        }

        $total_requested    = [];
        $total_po           = [];
        $material_item_ids  = [];

        
        foreach($component_items as $component_item){

            foreach($material_quantity_request_item[$component_item->id] as $mqri){

                foreach($material_quantity[$component_item->id] as $mq){

                    if($mqri->material_item_id == $mq->material_item_id){
                        
                        $total_requested[$component_item->id] = (object) [
                            'total' => $mqri->total * $mq->equivalent,
                            'unit'  => $component_item->unit
                        ];
                    }
                }
            
            }

            foreach($purchase_order_item[$component_item->id] as $poi){
                
                $material_item_ids[] = $poi->material_item_id;

                foreach($material_quantity[$component_item->id] as $mq){

                    if($poi->material_item_id == $mq->material_item_id){
                      
                        $total_po[$component_item->id] = (object) [
                            'total' => $poi->total_quantity * $mq->equivalent,
                            'unit'  => $component_item->unit
                        ];
                    }
                }
            }
                
        }

        $material_item_results = MaterialItem::whereIn('id',$material_item_ids)->get();
        $material_items = [];

        foreach($material_item_results as $mir){
            $material_items[$mir->id] = $mir;    
        }

        return view('reports/report_a/generate',[
            'project'               => $project,
            'section'               => $section,
            'component'             => $component,
            'component_items'       => $component_items,
            'total_requested'       => $total_requested,
            'total_po'              => $total_po,
            'purchase_order_item'   => $purchase_order_item,,
            'material_items'        => $material_items
        ]);
    }
}
