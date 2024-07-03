<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Project;
use App\Models\Section;
use App\Models\Component;
use App\Models\Supplier;

class ReportAController extends Controller
{
    public function select(){

        $projects = Project::orderBy('name','ASC')->get();

        return view('reports/report_a/select',[
            'projects' => $projects
        ]);
    }

    public function generate($project_id, $section_id, $component_id){

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
        return view('reports/report_a/generate',[
            'project'   => $project,
            'section'   => $section,
            'component' => $component
        ]);
    }
}
