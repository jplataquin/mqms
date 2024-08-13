<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Role;
use App\Models\RoleAccessCode;

class RoleAccessCodeController extends Controller
{
    
    

    public function _list($role_id){

        $role_id    = (int) $role_id;
        
        $result = DB::table('role_access_codes')
        ->join('access_codes', 'role_access_codes.access_code_id', '=', 'access_codes.id')
        ->where('role_access_codes.role_id',$role_id)->get();

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> $result
        ]);
    }

   
}