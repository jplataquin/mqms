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

        
        return view('/project_studio/overview',[
            'project' => $project,
            'section' => $section
        ]);
    }
}