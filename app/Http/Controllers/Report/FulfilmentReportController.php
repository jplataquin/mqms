<?php

namespace App\Http\Controllers\Report;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaterialQuantityRequest;

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

        $material_quantity_request = MaterialQuantityRequest::where('status','APRV')->where('date_created','>=',$from)->where('date_created','<=',$to)->get();

        return view('report/fulfilment/generate');
    }
}