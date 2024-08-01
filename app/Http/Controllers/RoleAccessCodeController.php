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
    
    public function _add(Request $request){

        $role_id         = (int) $request->input('role_id');
        $access_code_id  = (int) $request->input('access_code_id');

        $validator = Validator::make($request->all(),[
            'role_id' => [
                'required'
            ],
            'access_code_id' => [
                'required',
                Rule::unique('role_access_codes')->where(function ($query) use($role_id,$access_code_id) {
                    return $query->where('role_id', $role_id)
                    ->where('access_code_id', $access_code_id);
                })
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $roleAccessCode = new RoleAccessCode();

        $roleAccessCode->role_id                   = $role_id;
        $roleAccessCode->access_code_id            = $access_code_id;

        $roleAccessCode->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id'=> $roleAccessCode->id
            ]
        ]);

    }

    

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

    public function _delete(Request $request){

        $role_id           = (int) $request->input('role_id');
        $access_code_id    = (int) $request->input('access_code_id');

        $validator = Validator::make($request->all(),[
            'role_id' => [
                'required',
                'integer'
            ],
            'access_code_id' => [
                'required',
                'integer'
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        DB::table('role_access_codes')
        ->where('role_id',$role_id)
        ->where('access_code_id',$access_code_id)->delete();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }
}