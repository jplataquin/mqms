<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Supplier;
use App\Models\User;
use App\Models\MaterialQuantityRequestItem;
use App\Models\MaterialQuantityRequest;
use App\Models\PurchaseOrderItem;
use App\Models\MaterialItem;
use App\Models\MaterialGroup;
use Illuminate\Support\Facades\DB;

class MaterialItemReportController extends Controller
{

    public function paramters(){

        $material_groups = MaterialGroup::where('deleted_at',null)->get();

        return view('/report/material_item/parameters',[
            'material_groups' => $material_groups
        ]);
    }
}