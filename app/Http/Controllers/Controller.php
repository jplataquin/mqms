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

    protected function hasAccess(Array $codes){


        foreach($codes as $code){

            if(in_array($code,$this->accessCodes)){

                return true;
            }
        }

        return false;
    }
}
