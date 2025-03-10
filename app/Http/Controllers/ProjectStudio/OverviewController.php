<?php
namespace App\Http\Controllers\ProjectStudio;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Project;
use App\Models\Section;
use App\Models\ContractItem;
use App\Models\Component;
use App\Models\ComponentItem;
use App\Models\Unit;

class OverviewController extends Controller
{

    public function display($project_id,$section_id){

        $project = Project::findOrFail($project_id);
        $section = Section::where('project_id',$project_id)->where('id',$section_id)->where('deleted_at',null)->first();

        if(!$section){
           return abort(404);  
        }

        $data = [];

        $total_amount = (object) [
            'contract_item' => [],
            'component'     => []
        ];

        $contract_items = $section->ContractItems;

        //Contract Items
        foreach($contract_items as $contract_item){
            
            $components = $contract_item->Components;

            $data[$contract_item->id] = [
                'contract_item' => $contract_item,
                'components'    => []
            ];
            
            //Components
            foreach($components as $component){
                
                $total_amount['component'][$component->id] = [
                    'material' => 0,
                    'ref_1'    => 0
                ];

                $component_items = $component->ComponentItems;

                $data[$contract_item->id]['components'][$component->id] = [
                    'component'         => $component,
                    'component_items'   => []
                ];
                

                $component_item_material_total_amount   = 0;
                $component_item_ref_1_total_amount      = 0;
                //Component Items
                foreach($component_items as $component_item){

                    $data[$contract_item->id]['components'][$component->id]['component_items'][$component_item->id] = $component_item;
                    
                    $component_item_material_total_amount       += (float) $component_item->amount;
                    $component_item_ref_1_total_amount          += (float) $component_item->ref_1_amount;
                }

                 $total_amount->component[$component->id] = (object) [
                    'material' => $component_item_material_total_amount,
                    'ref_1'    => $component_item_ref_1_total_amount
                 ];
            }
        }
        
        $data = json_decode(json_encode($data));

        return view('/project_studio/overview',[
            'project'       => $project,
            'section'       => $section,
            'data'          => $data,
            'total_amount'  => $total_amount
        ]);
    }
}