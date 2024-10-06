<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Project;
use App\Models\Section;
use App\Models\Component;
use App\Models\Supplier;
use App\Models\User;
use App\Models\MaterialQuantityRequestItem;
use App\Models\MaterialQuantity;
use App\Models\PurchaseOrderItem;
use App\Models\MaterialItem;
use App\Models\ComponentUnit;
use App\Models\MaterialGroup;
use Illuminate\Support\Facades\DB;

class MaterialRequestReportController extends Controller
{
    public function parameters(){

        $projects = Project::where('deleted_at',null)->get();

        $material_groups = MaterialGroup::where('deleted_at',null)->get();

        $users = User::where('deleted_at',null)->get();

        return view('report/material_quantity/parameters',[
            'projects'          => $projects,
            'material_groups'   => $material_groups,
            'users'             => $users
        ]);
    }

    public function generate(Request $request){
        echo $request->input('material_items');
    }
}