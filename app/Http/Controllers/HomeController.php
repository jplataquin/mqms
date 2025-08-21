<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\CustomHelpers;
use App\Models\MaterialQuantityRequest;
use App\Models\MaterialCanvass;
use App\Models\PurchaseOrder;
use App\Models\Component;
use Carbon\Carbon;

class HomeController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {   

        $mytime         = Carbon::now();
        $current_datetime   = $mytime->toDateTimeString();

        $materialQuantityRequestPendCount   = MaterialQuantityRequest::where('status','=','PEND')->count();
        
        $materialCanvassPendCount           = MaterialCanvass::where('status','=','PEND')->groupBy('material_quantity_request_id')->selectRaw('count(*) as total')->count();

        $purchaseOrderPendCount             = PurchaseOrder::whereIn('status',['PEND','REVO'])->count();

        $componentPendCount                 = Component::where('status','=','PEND')->count();

        return view('home',[
            'materialQuantityRequestPendCount'  => $materialQuantityRequestPendCount,
            'materialCanvassPendCount'          => $materialCanvassPendCount,
            'purchaseOrderPendCount'            => $purchaseOrderPendCount,
            'componentPendCount'                => $componentPendCount,
            'current_datetime'                  => $current_datetime
        ]);
    }

    public function test(){
        return view('test',[
            'items' => [
                'apple1' => 'kwak',
                'apple2' => 'kwak',
                'apple3' => 'kwak',
                'apple4' => 'kwak',
                'apple5' => 'kwak',
                'apple6' => 'kwak',
                'apple7' => 'kwak',
                'apple8' => 'kwak',
                'apple9' => 'kwak',
                'apple10' => 'kwak',
                'apple11' => 'kwak',
                'apple12' => 'kwak',
                'apple122' => 'kwak',
                'apple4123123' => 'kwak',
                'apple234223423' => 'kwak',
                'apple123123' => 'kwak',
                'apple123123123231' => 'kwak',
                'apple12312312312' => 'kwak',
                'apple1222' => 'kwak',
                'apple11111' => 'kwak',
                'apple11123223123' => 'kwak',
                'appl1123123e' => 'kwak',
                'appl51e' => 'kwak',
                'apple123' => 'kwak',
                

            ]
        ]);
    }
}
