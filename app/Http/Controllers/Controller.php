<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Exceptions\MissingAccessCodeException;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $accessCodes = [];

    public function __construct(Request $request){

        $this->middleware(function ($request, $next) {
            
            $this->accessCodes = $request->accessCodes;

            return $next($request);
        });
  
    }

    protected function hasAccess($codes){

        if(!is_array($codes)){
            $codes = [$codes];
        }

        foreach($codes as $code){

            if(in_array($code,$this->accessCodes)){

                return true;
            }
        }

        return false;
    }

    /**
     * Require that the user has at least one of the specified access codes,
     * otherwise throw a MissingAccessCodeException.
     *
     * @param array|string $codes
     * @throws MissingAccessCodeException
     */
    protected function requireAccess($codes)
    {
        if (!$this->hasAccess($codes)) {
            throw new MissingAccessCodeException($codes);
        }
    }
}
