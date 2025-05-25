<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentTerm;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;


class PaymentTermController extends Controller
{
    public function create(){

        if(!$this->hasAccess('payment_term:own:create')){
            return view('access_denied');
        }

        return view('payment_term/create');
    }

    public function display($id){

        if(!$this->hasAccess('payment_term:all:view')){
            return view('access_denied');
        }

        $id = (int) $id;

        $paymentTerm = PaymentTerm::findOrFail($id);


        return view('payment_term/display',[
            'paymentTerm' => $paymentTerm
        ]);
    }


    public function list(){

        return view('payment_term/list');
    }


    public function _create(Request $request){

        if(!$this->hasAccess('payment_term:all:create')){
            return response()->json([
                'status'    => 0,
                'message'   => 'Access Denied',
                'data'      => []
            ]);
        }

        $text   = $request->input('text') ?? '';

        $validator = Validator::make($request->all(),[
            'text' => [
                'required',
                'max:255',
                'unique:payment_terms'
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

        $paymentTerm = new PaymentTerm();

        $paymentTerm->text          = $text;
        $paymentTerm->created_by    = $user_id;
    

        $paymentTerm->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id'=> $paymentTerm->id
            ]
        ]);

    }

    public function _update(Request $request){

        if(!$this->hasAccess('payment_term:all:update')){
            return response()->json([
                'status'    => 0,
                'message'   => 'Access Denied',
                'data'      => []
            ]);
        }

        $id       = (int) $request->input('id') ?? 0;
        $text     = $request->input('text') ?? '';
        
        $validator = Validator::make($request->all(),[
            'id'   => [
                'required',
                'integer',               
            ],
            'text' => [
                'required',
                'max:255',
                Rule::unique('payment_terms')->ignore($id),
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
        $paymentTerm = PaymentTerm::find($id);

        if(!$paymentTerm){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => [
                    'id' => $id
                ]
            ]);
        }

        $paymentTerm->text                         = $text;
        $paymentTerm->updated_by                   = $user_id;

        $paymentTerm->save();


        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id' => $id
            ]
        ]);

    }

    public function _list(Request $request){

        //todo check role


        $page       = (int) $request->input('page')     ?? 1;
        $limit      = (int) $request->input('limit')    ?? 10;
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $query      = $request->input('query')          ?? '';
        $result = [];

        $paymentTerm = new PaymentTerm();

        if($query != ''){
            $paymentTerm = $paymentTerm->where('text','LIKE','%'.$query.'%');
        }

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $paymentTerm->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $paymentTerm->orderBy($orderBy,$order)->take($limit)->get();
        }

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> $result
        ]);
    }

    public function _delete(Request $request){

        if(!$this->hasAccess('payment_term:all:delete')){
            return response()->json([
                'status'    => 0,
                'message'   => 'Access Denied',
                'data'      => []
            ]);
        }

        $id = (int) $request->input('id');


        $validator = Validator::make($request->all(),[
            'id' => [
                'required',
                'integer',
            ]
        ]);

        if($validator->fails()){
            
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $paymentTerm = PaymentTerm::find($id);

        if(!$paymentTerm){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }
        
        $user_id = Auth::user()->id;
        
        $paymentTerm->deleted_by = $user_id;
        $paymentTerm->save();

        //Soft delete
        if(!$paymentTerm->delete()){
           
           return response()->json([
               'status'    => 0,
               'message'   => '' ,
               'data'      => []
           ]);
        }


        return response()->json([
           'status'    => 1,
           'message'   => '',
           'data'      => []
       ]);
    }
}
