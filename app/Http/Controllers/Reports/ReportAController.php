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
}
