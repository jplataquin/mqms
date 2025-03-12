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

    protected $accessCodes = ['a'];

    public function __construct(Request $request){

        $c = $this;

        $this->middleware(function ($request, $next) use ($c) {
            $this->accessCodes= $request->accessCodes;

            return $next($request);
        });
  
    }

    protected function checkAccessCode($asset='',$scope='',$action=''){

        echo 'show access codes <br>';

        print_r($this->accessCodes);
    }
}
