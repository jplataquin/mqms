<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function create(){

        return view('supplier/create');
    }

    public function display($id){

        $id = (int) $id;

        $supplier = Supplier::findOrFail($id);

        return view('supplier/display',[
            'supplier' => $supplier
        ]);
    }


    public function list(){

        return view('supplier/list');
    }


    public function _create(Request $request){

        //todo check role

        //TODO add city and status

        $name                       = $request->input('name') ?? '';
        $address                    = $request->input('address') ?? '';
        $primary_email              = $request->input('primary_email') ?? '';
        $primary_contact_no         = $request->input('primary_contact_no') ?? '';
        $primary_contact_person     = $request->input('primary_contact_person') ?? '';
        $secondary_email            = $request->input('secondary_email') ?? '';
        $secondary_contact_no       = $request->input('secondary_contact_no') ?? '';
        $secondary_contact_person   = $request->input('secondary_contact_person') ?? '';

        $validator = Validator::make($request->all(),[
            'name' => [
                'required',
                'max:255',
                'unique:suppliers'
            ],
            'address' => [
                'required',
                'max:500'
            ],
            'primary_email' => [
                'required',
                'email'
            ],
            'primary_contact_no' => [
                'required',
                'max:255'
            ],
            'primary_contact_person' => [
                'required',
                'max:255'
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

        $supplier = new Supplier();

        $supplier->name                               = $name;
        $supplier->address                            = $address;
        $supplier->primary_contact_no                 = $primary_contact_no;
        $supplier->primary_email                      = $primary_email;
        $supplier->primary_contact_person             = $primary_contact_person;
        $supplier->secondary_contact_no               = $secondary_contact_no;
        $supplier->secondary_email                    = $secondary_email;
        $supplier->secondary_contact_person           = $secondary_contact_person;
        $supplier->created_by                         = $user_id;
    

        $supplier->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id'=> $supplier->id
            ]
        ]);

    }

    public function _update(Request $request){

        //todo check role
        //Todo add city and status;
        $id                         = (int) $request->input('id') ?? 0;
        $name                       = $request->input('name') ?? '';
        $address                    = $request->input('address') ?? '';
        $primary_email              = $request->input('primary_email') ?? '';
        $primary_contact_no         = $request->input('primary_contact_no') ?? '';
        $primary_contact_person     = $request->input('primary_contact_person') ?? '';
        $secondary_email            = $request->input('secondary_email') ?? '';
        $secondary_contact_no       = $request->input('secondary_contact_no') ?? '';
        $secondary_contact_person   = $request->input('secondary_contact_person') ?? '';

        $validator = Validator::make($request->all(),[
            'id'   => [
                'required',
                'integer',               
            ],
            'name' => [
                'required',
                'max:255',
                Rule::unique('suppliers')->ignore($id),
            ],
            'address' => [
                'required',
                'max:500'
            ],
            'primary_email' => [
                'required',
                'email'
            ],
            'primary_contact_no' => [
                'required',
                'max:255'
            ],
            'primary_contact_person' => [
                'required',
                'max:255'
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
        $supplier = Supplier::find($id);

        if(!$supplier){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => [
                    'id' => $id
                ]
            ]);
        }

        $supplier->name                               = $name;
        $supplier->address                            = $address;
        $supplier->primary_contact_no                 = $primary_contact_no;
        $supplier->primary_email                      = $primary_email;
        $supplier->primary_contact_person             = $primary_contact_person;
        $supplier->secondary_contact_no               = $secondary_contact_no;
        $supplier->secondary_email                    = $secondary_email;
        $supplier->secondary_contact_person           = $secondary_contact_person;
        $supplier->updated_by                         = $user_id;

        $supplier->save();


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

        $supplier = new Supplier();

        if($query != ''){
            $supplier = $supplier->where('name','LIKE','%'.$query.'%');
        }

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $supplier->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $supplier->orderBy($orderBy,$order)->take($limit)->get();
        }

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> $result
        ]);
    }

    public function _delete(Request $request){

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

        $supplier = Supplier::find($id);

        if(!$supplier){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }
        
       
        if(!$supplier->delete()){
           
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
