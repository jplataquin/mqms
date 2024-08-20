<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;

class UserController extends Controller
{
    
    public function create(){
        return view('user/create');
    }

    public function _create(Request $request){
           //todo check role

           $name        = $request->input('name') ?? '';
           $email       = $request->input('email') ?? '';
           $password    = $request->input('password') ?? '';
           $repassword  = $request->input('repassword') ?? '';

           $validator = Validator::make($request->all(),[
               'name' => [
                   'required',
                   'max:255'
               ],
               'email' => [
                   'required',
                   'email',
                   'unique:users,email'
               ],
               'password' => [
                    'required',
                    'min:6',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*(_|[^\w])).+$/'
               ],
               'repassword' => [
                    'required_with:password',
                    'same:password'
               ]
           ],[
                'password.regex' => 'The password must contain 1 lowercase AND 1 uppercase AND 1 number AND 1 symbol'
           ]);
   
           if ($validator->fails()) {
               return response()->json([
                   'status'    => -2,
                   'message'   => 'Failed Validation',
                   'data'      => $validator->messages()
               ]);
           }
           

           $user_id = Auth::user()->id;

           $user = new User();

           $user->name              = $name;
           $user->email             = $email;
           $user->status            = 'ACTV';
           $user->password          = Hash::make($password);;
           $user->created_by        = $user_id;
           $user->reset_password    = 1;

           $user->save();

           return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id' => $user->id
            ]
        ]);
    }

    public function display($id){

        $id = (int) $id;
        $user = User::findOrFail($id);

        $status_options = $user->statusOptions();

        $roles = Role::all();


        return view('user/display',[
            'user'              => $user,
            'status_options'    => $status_options,
            'roles'             => $roles
        ]);
    }

    public function list(){

        return view('user/list');
    }

       
    public function _list(Request $request){

        //todo check role

        $page       = (int) $request->input('page')     ?? 1;
        $limit      = (int) $request->input('limit')    ?? 10;
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $query      = $request->input('query')          ?? '';
        $result = [];

        $user = new User();

        if($query != ''){
            $user = $user->where('email','LIKE','%'.$query.'%')->orWhere('name','LIKE','%'.$query.'%');
        }

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $user->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $user->orderBy($orderBy,$order)->take($limit)->get();
        }

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> $result
        ]);
    }

    public function _reset_password(Request $request){
        //todo check role

        $id = (int) $request->input('id') ?? 0;

        $validator = Validator::make($request->all(),[
            'id' => [
                'required',
                'integer',
                'gte:1'
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }


         $user = User::find($id);   

         if(!$user){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found'
            ]);
         }

         if($user->deleted_at != null){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found'
            ]);
         }

         $user_id = Auth::user()->id;

         $user->reset_password = 1;
         $user->updated_by     = $user_id;
         $user->save();

         return response()->json([
            'status'    => 1,
            'message'   => '',
            'data' => [
                'id' => $user->id
            ]
        ]);
    }


    public function _add_role(Request $request){
        
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

    public function _remove_role(Request $request){

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

    public function _roles($id){

        $id = (int) $id;
        
        $result = DB::table('user_roles')
        ->join('roles', 'user_roles.role_id', '=', 'roles.id')
        ->where('user_roles.user_id',$id)->get();

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> $result
        ]);
    }

    public function reset_password(){

        return view('/user/reset_password',[
            'user' => Auth::user()
        ]);
    }

   
}