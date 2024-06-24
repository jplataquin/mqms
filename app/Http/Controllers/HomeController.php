<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\CustomHelpers;
use App\Models\MaterialQuantityRequest;
use App\Models\MaterialCanvass;
use App\Models\PurchaseOrder;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {   
        $materialQuantityRequestPendCount = MaterialQuantityRequest::where('status','=','PEND')->count();
        
        $materialCanvassPendCount = MaterialCanvass::where('status','=','PEND')->groupBy('material_quantity_request_id')->selectRaw('count(*) as total')->count();

        $purchaseOrderPendCount = PurchaseOrder::where('status','=','PEND')->count();

        return view('home',[
            'materialQuantityRequestPendCount'  => $materialQuantityRequestPendCount,
            'materialCanvassPendCount'          => $materialCanvassPendCount,
            'purchaseOrderPendCount'            => $purchaseOrderPendCount
        ]);
    }
}
