<?php

namespace App\Http\Controllers\Report;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaterialQuantityRequest;
use App\Models\PurchaseOrder;
use Carbon\Carbon;

class FulfilmentReportController extends Controller
{

    public function parameters(){

        return view('report/fulfilment/parameters');
    }

    public function generate(Request $request){

        $from = $request->input('from');
        $to   = $request->input('to');

        $from = $from.' 00:00:00';
        $to   = $to.' 23:59:59';

        $material_quantity_request = MaterialQuantityRequest::where('status','APRV')->where('approved_at','>=',$from)->where('approved_at','<=',$to)->get();

        $request_count  = 0;
        $target_hit     = 0;
        $target_missed  = 0;

        foreach($material_quantity_request as $mqr){

            $request_count++;

            $start = Carbon::parse($mqr->approved_at);


            $purchase_order = PurchaseOrder::where('status','APRV')->where('material_quantity_request_id',$mqr->id)->get();
            

            foreach($purchase_order as $po){
                $end = Carbon::parse($po->approved_at);
                
                $days = $start->diffInDays($end);

                if($days <= 7){
                    $target_hit++;
                }else{
                    $target_missed++;
                }
            }
        }

        return view('report/fulfilment/generate',[
            'request_count' => $request_count,
            'target_hit'    => $target_hit,
            'target_missed' => $target_missed
        ]);
    }
}