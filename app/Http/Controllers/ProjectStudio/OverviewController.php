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

    public function display($project_id){

        $project = Project::findOrFail($project_id);

        return view('/project_studio/overview',[
            'project' => $project
        ]);
    }
}