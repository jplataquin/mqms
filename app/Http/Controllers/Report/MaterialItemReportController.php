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
use App\Models\MaterialCanvass;
use App\Models\MaterialGroup;
use App\Models\PaymentTerm;
use Illuminate\Support\Facades\DB;

class MaterialItemReportController extends Controller
{

    public function parameters(){

        $material_groups = MaterialGroup::where('deleted_at',null)->get();

        return view('/report/material_item/parameters',[
            'material_groups' => $material_groups
        ]);
    }

    public function generate(Request $request){
        $material_item_id_arr = explode(',',$request->input('material_items'));

        $material_request_items = MaterialQuantityRequestItem::whereIn('material_item_id',$material_item_id_arr)
        ->where('status','APRV')
        ->orderBy('created_at','DESC')
        ->get();

        $result = [];

        $material_request_item_id_arr = [];

        foreach($material_request_items as $mr_item){

            $mc = MaterialCanvass::where('material_quantity_request_item_id',$mr_item->id)
            ->where('status','APRV')->first();

            if(!$mc) continue;

            //Set material_item_id grouping
            if(!isset($result[$mr_item->material_item_id])){
                $result[$mr_item->material_item_id] = [];
            }

            //Set supplier_id grouping
            if(! isset( $result[$mr_item->material_item_id][$mc->supplier_id] ) ){
                $result[$mr_item->material_item_id][$mc->supplier_id] = [];
            }

            //Set payment_term_id grouping
            if(! isset($result[$mr_item->material_item_id][$mc->supplier_id][$mc->payment_term_id]) ){
                $result[$mr_item->material_item_id][$mc->supplier_id][$mc->payment_term_id] = [];
            }

            //Set price grouping
            if(! isset( $result[$mr_item->material_item_id][$mc->supplier_id][$mc->payment_term_id][$mc->price] ) ){
                $result[$mr_item->material_item_id][$mc->supplier_id][$mc->payment_term_id][$mc->price] = $mc->created_at;
            }

            $result[$mr_item->material_item_id][$mc->supplier_id][$mc->payment_term_id][$mc->price] = $mc->created_at
        }

        $supplier_options       = Supplier::toOptions();
        $payment_term_options   = PaymentTerm::toOptions();
        $material_item_options  = MaterialItem::toOptions();

        return view('/report/material_item/generate',[
            'result'                => $result,
            'supplier_options'      => $supplier_options,
            'payment_term_options'  => $payment_term_options,
            'material_item_options' => $material_item_options
        ]);

    }
}