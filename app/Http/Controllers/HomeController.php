<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\CustomHelpers;
use App\Models\MaterialQuantityRequest;
use App\Models\MaterialCanvass;
use App\Models\PurchaseOrder;
use App\Models\Component;

class HomeController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {   

        print_r($this->currentUser);
        
        $materialQuantityRequestPendCount   = MaterialQuantityRequest::where('status','=','PEND')->count();
        
        $materialCanvassPendCount           = MaterialCanvass::where('status','=','PEND')->groupBy('material_quantity_request_id')->selectRaw('count(*) as total')->count();

        $purchaseOrderPendCount             = PurchaseOrder::whereIn('status',['PEND','REVO'])->count();

        $componentPendCount                 = Component::where('status','=','PEND')->count();

        return view('home',[
            'materialQuantityRequestPendCount'  => $materialQuantityRequestPendCount,
            'materialCanvassPendCount'          => $materialCanvassPendCount,
            'purchaseOrderPendCount'            => $purchaseOrderPendCount,
            'componentPendCount'                => $componentPendCount
        ]);
    }
}
