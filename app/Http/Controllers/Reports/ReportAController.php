<?php

namespace App\Http\Controllers\reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Project;
use App\Models\Section;
use App\Models\Component;
use App\Models\Supplier;

class ReportAController extends Controller
{
    public function index(){

        $projects = Project::orderBy('name','ASC')->get();

        return view('reports/select',[
            'projects' => $projects
        ]);
    }
}
