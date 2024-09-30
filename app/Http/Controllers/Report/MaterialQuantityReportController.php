<?php

namespace App\Http\Controllers\Report;

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
use App\Models\ComponentUnit;
use Illuminate\Support\Facades\DB;

class MaterialQuantityReportController extends Controller
{
    public function parameters(){

        $projects = Project::where('deleted_at','!=',null)->get();

        $item_groups = ItemGroup::where('deleted_at','!=',null)->get();

        return view('report/material_quantity/paramters',[
            'projects'      => $projects,
            'item_groups'   => $item_groups
        ]);
    }
}