<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CouponController extends Controller
{
    public function create(){

        return view('coupon/create');
    }

    public function _create(Request $request){
        

        // if(!$this->hasAccess('coupon:own:create')){
        //     return response()->json([
        //         'status'    => 0,
        //         'message'   => 'Access Denied',
        //         'data'      => []
        //     ]);
        // }


        $amount = $request->input('amount');

        $validator = Validator::make($request->all(),[
            'amount' =>[
                'required',
                'numeric',
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

        $user_id    = Auth::user()->id;
        $salt       = Str::random(16);

        $coupon = new Coupon();

        $coupon->amount         = $amount;
        $coupon->status         = 'PEND';
        $coupon->salt           = $salt;
        $coupon->code           = $coupon->generateCode($salt,$amount);
        $coupon->created_by     = $user_id;

        $coupon->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id' => $coupon->id
            ]
        ]);
        
    }


    public function display($id){

        //TODO roles 

        $coupon = Coupon::findOrFail($id);

        $coupon_details = [
            'ID'            => str_pad($coupon->id,4,0,STR_PAD_LEFT),
            'status'        => $coupon->status,
            'Code'          => $coupon->code,
            'Created By'    => $coupon->CreatedByUser()->name.' '.$coupon->created_at
        ];

        if($coupon->updated_at && $coupon->updated_by){
            $coupon_details['Updated By'] = $coupon->UpdatedByUser()->name.' '.$coupon->updated_at;
        }

        if($coupon->approved_at && $coupon->status == 'APRV'){
            $coupon_details['Approved By'] = $coupon->ApprovedByUser()->name.' '.$coupon->approved_at;
        }

        if($coupon->rejected_at && $coupon->status == 'REJC'){
            $coupon_details['Rejected By'] = $coupon->RejectedByUser()->name.' '.$coupon->rejected_at;
        }

        if($coupon->request_void_at && $coupon->status == 'REVO'){
            $coupon_details['Request Void By'] = $coupon->RequestVoidByUser()->name.' '.$coupon->request_void_at;
        }

        if($coupon->void_at && $coupon->status == 'VOID'){
            $coupon_details['Void By'] = $coupon->VoidByUser()->name.' '.$coupon->void_at;
        }

        if($coupon->claimed_at && $coupon->satatus == 'CLAI'){
            $coupon_details['Claimed By'] = $coupon->claimed_by_name.' '.$coupon->claimed_at;
        }

        if($coupon->processed_at){
            $coupon_details['Processed By'] = $coupon->ProcessedByUser()->name.' '.$coupon->processed_at;
        }

        return view('coupon/display',[
            'coupon'            => $coupon,
            'coupon_details'    => $coupon_details
        ]);
    }


    public function generate($id){
        
        $coupon = Coupon::findOrFail($id);

        if($coupon->status != 'APRV'){
            return view('coupon/error_print',[
                'coupon' => $coupon
            ]);
        }

        return view('coupon/generate',[
            'coupon' => $coupon
        ]);
    }


    public function _update(Request $request){
        
        $amount = $request->input('amount');
        $id     = (int) $request->input('id');

        
        $coupon = Coupon::find($id);

        if(!$coupon){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }

        if($coupon->status != 'PEND'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record can no longer be updated',
                'data'      => []
            ]);
        }

        $validator = Validator::make($request->all(),[
            'amount' => [
                'required',
                'numeric',
                'gte:1'
            ],
            'id' => [
                'required',
                'numeric',
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

        $user_id    = Auth::user()->id;
        $salt       = Str::random(16);

        $code           = $coupon->generateCode($salt,$amount);
        
        $coupon->amount     = $amount;
        $coupon->code       = $code;
        $coupon->updated_by = $user_id; 

        $coupon->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id' => $coupon->id
            ]
        ]);
    }
    

    public function list(){

        $users = User::orderBy('name','ASC')->get();

        return view('coupon/list',[
            'users' => $users
        ]);
    }

    public function _list(Request $request){

         //todo check role

         $page              = (int) $request->input('page')     ?? 1;
         $limit             = (int) $request->input('limit')    ?? 10;
         $orderBy           = $request->input('order_by')       ?? 'id';
         $order             = $request->input('order')          ?? 'DESC';
         
         $from              = $request->input('from')           ?? '';
         $to                = $request->input('to')             ?? '';
         $status            = $request->input('status')         ?? '';
         $date_type         = $request->input('date_type')      ?? '';

         $created_by        = (int) $request->input('created_by');

         $validator = Validator::make($request->all(),[
            'created_by' =>[
                'nullable',
                'integer',
                'gte:1'
            ],
            'from' => [
                'nullable',
                'date_format:Y-m-d'
            ],
            'to' => [
                'nullable',
                'date_format:Y-m-d'
            ]
        ]);

        if($validator->fails()){
            
             return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
            
        }

        $result = [];
 
        $coupon = new Coupon();
 
        if($created_by){
            $coupon = $coupon->where('created_by',$created_by);
        }

        if($status){
            $coupon = $coupon->where('status',$status);
        }

        if($from && $date_type){
            $coupon = $coupon->where($date_type,'>=',$from.' 00:00:00');
        }

        if($to && $date_type){
            $coupon = $coupon->where($date_type,'<=',$to.' 23:59:59');
        }
 
        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $coupon->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $coupon->orderBy($orderBy,$order)->get();
        }

        foreach($result as $i => $res){

            $result[$i]['created_by_name'] = $res->createdByUser()->name; 
        }
 
        return response()->json([
            'status'    => 1,
            'message'   =>'',
            'data'      => $result
        ]);
    }


    public function _delete(Request $request){
        
        $id = (int) $request->input('id') ?? 0;

        $coupon = Coupon::find($id);

        if(!$coupon){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }

        if($coupon->status != 'PEND'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record can no longer be deleted, status is '.$coupon->status,
                'data'      => []
            ]);
        }

        $user_id    = Auth::user()->id;
        
        $coupon->deleted_by = $user_id;
        $coupon->save();
        
        $coupon->delete();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }

    public function _request_void(Request $request){
        
        $id = (int) $request->input('id') ?? 0;

        $coupon = Coupon::find($id);

        if(!$coupon){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }

        if($coupon->status != 'APRV'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record can no longer be requested for voiding, status is '.$coupon->status,
                'data'      => []
            ]);
        }

        $user_id    = Auth::user()->id;
        
        $coupon->status          = 'REVO';
        $coupon->request_void_by = $user_id;
        $coupon->request_void_at = Carbon::now();;
        
        $coupon->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }

    public function claim($id,$code){
        
        $id = (int) $id;
        
        $coupon = Coupon::findOrFail($id);

        $salt = $coupon->salt;

        $correct_code = $coupon->generateCode($salt,$coupon->amount);
        

        $flag = 'valid';

        if($coupon->status != 'APRV'){
        
            $flag = 'invalid';
        
        }else if($correct_code != $code){
        
            $flag = 'invalid';

        }else if($coupon == 'CLAI'){
            $flag = 'claimed';
        }


          $coupon_details = [
            'ID'            => str_pad($coupon->id,4,0,STR_PAD_LEFT),
            'status'        => $coupon->status,
            'Code'          => $coupon->code,
            'Created By'    => $coupon->CreatedByUser()->name.' '.$coupon->created_at,
            'correct_code'  => $correct_code
        ];

        if($coupon->updated_at && $coupon->updated_by){
            $coupon_details['Updated By'] = $coupon->UpdatedByUser()->name.' '.$coupon->updated_at;
        }

        if($coupon->approved_at && $coupon->status == 'APRV'){
            $coupon_details['Approved By'] = $coupon->ApprovedByUser()->name.' '.$coupon->approved_at;
        }

        if($coupon->rejected_at && $coupon->status == 'REJC'){
            $coupon_details['Rejected By'] = $coupon->RejectedByUser()->name.' '.$coupon->rejected_at;
        }

        if($coupon->request_void_at && $coupon->status == 'REVO'){
            $coupon_details['Request Void By'] = $coupon->RequestVoidByUser()->name.' '.$coupon->request_void_at;
        }

        if($coupon->void_at && $coupon->status == 'VOID'){
            $coupon_details['Void By'] = $coupon->VoidByUser()->name.' '.$coupon->void_at;
        }

        if($coupon->claimed_at && $coupon->satatus == 'CLAI'){
            $coupon_details['Claimed By'] = $coupon->claimed_by_name.' '.$coupon->claimed_at;
        }

        if($coupon->processed_at){
            $coupon_details['Processed By'] = $coupon->ProcessedByUser()->name.' '.$coupon->processed_at;
        }


        return view('coupon/claim',[
            'flag'              => $flag,
            'coupon'            => $coupon,
            'coupon_details'    => $coupon_details
        ]);
    }

    

}
