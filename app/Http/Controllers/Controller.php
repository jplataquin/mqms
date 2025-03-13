<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $accessCodes = [];

    public function __construct(Request $request){

        $this->middleware(function ($request, $next) {
            
            $this->accessCodes= $request->accessCodes;

            return $next($request);
        });
  
    }

    protected function hasAccess($asset='',$scope='',$action=''){

        $target_code = $asset.':'.$scope.':'.$action;

        if(!in_array($target_code,$this->checkAccessCode)){


            return false;
            
            // if (Request::wantsJson()) {
                
            //     return response()->json([
            //         'status'    => 0,
            //         'message'   => 'Restricted Action',
            //         'data'      => [
            //             'need' =>  $target_code 
            //         ]
            //     ]);


            // } else {
            //     // return HTML response
            // }

    
        }

        return true;
    }
}
