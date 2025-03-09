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

        $contract_items = $section->ContractItems;

        foreach($contract_items as $contract_item){
            
            $components = $contract_item->Components;

            $data[$contract_item->id] = [
                'contract_item' => $contract_item,
                'components'    => []
            ];
            
            foreach($components as $component){
                
                $component_items = $component->ComponentItems;

                $data[$contract_item->id]['components'][$component->id] = [
                    'component'         => $component,
                    'component_items'   => []
                ];

                foreach($component_items as $component_item){

                    $data[$contract_item->id]['components'][$component->id]['component_items'][$component_item->id] = $component_item;
                    
                }
            }
        }
        
        return view('/project_studio/overview',[
            'project' => $project,
            'section' => $section,
            'data'    => $data
        ]);
    }
}