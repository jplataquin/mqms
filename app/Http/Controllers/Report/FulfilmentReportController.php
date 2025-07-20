<?php

namespace App\Http\Controllers\Report;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FulfilmentReportController extends Controller
{

    public function parameters(){

        return view('report/fulfilment/parameters');
    }

    public function generate(Request $request){

        $from = $request->input('from');
        $to   = $request->input('to');
        
        return view('report/fulfilment/generate');
    }
}