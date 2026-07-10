<?php

namespace App\Http\Controllers\Report;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaterialQuantityRequest;
use App\Models\PurchaseOrder;
use Carbon\Carbon;

class FulfilmentReportController extends Controller
{
    public function __construct(){
        $this->threshold = 30;
    }

    public function parameters(){

        return view('report/fulfilment/parameters');
    }

    private function process(){

    
        $material_quantity_request = MaterialQuantityRequest::where('status','APRV')->where('approved_at','>=',$from)->where('approved_at','<=',$to)->get();

        $request_count  = 0;
        $target_hit     = 0;
        $target_missed  = 0;
        $missed_entrires = [];

        foreach($material_quantity_request as $mqr){

            $request_count++;

            $start = Carbon::parse($mqr->approved_at);


            $purchase_order = PurchaseOrder::where('status','APRV')->where('material_quantity_request_id',$mqr->id)->get();
            
            $hit_flag = true;
            
            $missed_po_list = [];

            foreach($purchase_order as $po){
                $end = Carbon::parse($po->approved_at);
                
                $days = $start->diffInDays($end);

                if($days > $this->threshold){
                    
                    $hit_flag = false;
                    $missed_po_list[] = $po->id;
                }
            }

            if($hit_flag){
                $target_hit++;
            }else{
                $target_missed++;
                
                if(!isset($missed_entrires[$mqr->id])){
                    $missed_entrires[$mqr->id] = [];
                }

                $missed_entrires[$mqr->id] = $missed_po_list;
            }
        }

        $percentage = 0;

       
        if($request_count > 0){

            $percentage = ($target_hit / $request_count) * 100;

            $percentage = ceil($percentage);
        }

      

        return [
            'request_count'     => $request_count,
            'target_hit'        => $target_hit,
            'target_missed'     => $target_missed,
            'percentage'        => $percentage,
            'from'              => $from,
            'to'                => $to,
            'missed_entries'    => $missed_entrires,
        ];
    }
    public function generate(Request $request){

        $from_input = $request->input('from');
        $to_input   = $request->input('to');

        $from = $from_input.' 00:00:00';
        $to   = $to_input.' 23:59:59';

        $data = $this->process($from,$to);

        return view('report/fulfilment/generate',$data);
    }


    public function print(Request $request){

        $from_input = $request->input('from');
        $to_input   = $request->input('to');

        $from = $from_input.' 00:00:00';
        $to   = $to_input.' 23:59:59';

        $data = $this->process($from,$to);
        
        return view('report/fulfilment/print',$data);
    }
}