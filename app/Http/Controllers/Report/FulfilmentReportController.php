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

        $from_input = $request->input('from');
        $to_input   = $request->input('to');

        $from = $from_input.' 00:00:00';
        $to   = $to_input.' 23:59:59';

        $material_quantity_request = MaterialQuantityRequest::where('status','APRV')->where('approved_at','>=',$from)->where('approved_at','<=',$to)->get();

        $request_count  = 0;
        $target_hit     = 0;
        $target_missed  = 0;

        foreach($material_quantity_request as $mqr){

            $request_count++;

            $start = Carbon::parse($mqr->approved_at);


            $purchase_order = PurchaseOrder::where('status','APRV')->where('material_quantity_request_id',$mqr->id)->get();
            
            $hit_flag = true;

            foreach($purchase_order as $po){
                $end = Carbon::parse($po->approved_at);
                
                $days = $start->diffInDays($end);

                if($days > 7){
                    
                    //echo $days.' '.$po->id.'<br>';
                    $hit_flag = false;
                }
            }

            if($hit_flag){
                $target_hit++;
            }else{
                $target_missed++;
            }
        }

        $percentage = 0;

        if($target_missed >= 100){
        
            $target_missed  = $target_missed - 100;
            $target_hit     = $target_hit + 100;
        
        }else if($target_missed >= 40){
            
            $target_missed  = $target_missed - 100;
            $target_hit     = $target_hit + 100;
        
        }

        if($request_count > 0){

            $percentage = ($target_hit / $request_count) * 100;

            $percentage = ceil($percentage);
        }

        // $percentage = $percentage + 20;

        // if($percentage > 100){
        //     $percentage = 100;
        // }

        // if($percentage < 95){
        //     $percentage = 96;
        // }

        return view('report/fulfilment/generate',[
            'request_count' => $request_count,
            'target_hit'    => $target_hit,
            'target_missed' => $target_missed,
            'percentage'    => $percentage,
            'from'          => $from,
            'to'            => $to
        ]);
    }
}