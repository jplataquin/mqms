<?php

namespace App\Http\Controllers\Report;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MaterialRequestReportController extends Controller
{

    public function parameters(){

        return view('report/fulfilment/parameters');
    }
}