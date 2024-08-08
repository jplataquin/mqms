<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\User;

class UserController extends Controller
{
    
    public function create(){
        return view('user/create');
    }

    public function _create(Request $request){
           //todo check role

           $name    = $request->input('name') ?? '';
           $email   = $request->input('email') ?? '';

           $validator = Validator::make($request->all(),[
               'name' => [
                   'required',
                   'max:255'
               ],
               'email' => [
                   'required',
                   'email',
                   'unique:users,email'
               ]
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
           $user->password          = 'RESET';
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

        return view('user/display',[
            'user'              => $user,
            'status_options'    => $status_options
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

   
}