<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;

class UserRoleController extends Controller
{
    
    public function display($id){

        $id = (int) $id;

        $user = User::findOrFail($id);

        $roles = Role::all();

        return view('user_role/display',[
            'user' => $user,
            'roles' => $roles
        ]);
    }


    public function list(){
        return view('user_role/list');
    }

    public function _add(Request $request){

        $role_id        = (int) $request->input('role_id');
        $user_id        = (int) $request->input('user_id');

        $validator = Validator::make($request->all(),[
            'user_id' => [
                'required',
                'integer'
            ],
            'role_id' => [
                'required',
                'integer',
                Rule::unique('user_roles')->where(function ($query) use($role_id,$user_id) {
                    return $query->where('role_id', $role_id)
                    ->where('user_id', $user_id);
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

        $userRole = new UserRole();

        $userRole->role_id            = $role_id;
        $userRole->user_id            = $user_id;

        $userRole->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id'=> $userRole->id
            ]
        ]);

    }

    

    public function _list($user_id){

        $user_id    = (int) $user_id;
        
        $result = DB::table('user_roles')
        ->join('roles', 'user_roles.role_id', '=', 'roles.id')
        ->where('user_roles.user_id',$user_id)->get();

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> $result
        ]);
    }

    public function _delete(Request $request){

        $role_id    = (int) $request->input('role_id');
        $user_id    = (int) $request->input('user_id');

        $validator = Validator::make($request->all(),[
            'role_id' => [
                'required',
                'integer'
            ],
            'user_id' => [
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

        DB::table('user_roles')
        ->where('role_id',$role_id)
        ->where('user_id',$user_id)->delete();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }
}