<?php

namespace App\Http\Controllers\Review;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Coupon;
use App\Models\User;


class CouponReviewController extends Controller
{

    public function display($id){

        //TODO roles 

        $coupon = Coupon::findOrFail($id);

        $coupon_details = [
            'ID'            => str_pad($coupon->id,6,0,STR_PAD_LEFT),
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


    public function list(){

        $users = User::orderBy('name','ASC')->get();

        return view('review/coupon/list',[
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

        $coupon = $coupon->where('status','PEND');
        

        if($from){
            $coupon = $coupon->where('created_at','>=',$from.' 00:00:00');
        }

        if($to){
            $coupon = $coupon->where('created_at','<=',$to.' 23:59:59');
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


    public function _approve(Request $request){

        // if(!$this->hasAccess('coupon:all:approve')){
        //     return response()->json([
        //         'status'    => 0,
        //         'message'   => 'Access Denied',
        //         'data'      => []
        //     ]);
        // }

        $id     = (int) $request->input('id') ?? 0;
        $amount = $request->input('amount');

        $validator = Validator::make($request->all(),[
            'id' => [
                'required',
                'integer'
            ],
            'amount' => [
                'required'
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $coupon = Coupon::find($id);

        if(!$coupon){
            return response()->json([
                'status'    => 0,
                'message'   =>'Error: Record not found',
                'data'      => []
            ]);
        }

        if($coupon->status != 'PEND'){
            return response()->json([
                'status'    => 0,
                'message'   =>'Cannot approve this record, the status status is '.$coupon->status,
                'data'      => []
            ]);
        }

        //Prevents race conditions when approving
        if($coupon->amount != $amount){
            return response()->json([
                'status'    => 0,
                'message'   => 'The record has been altred',
                'data'      => []
            ]);
        }

        
        $user_id = Auth::user()->id;

        $coupon->status      = 'APRV';
        $coupon->approved_by = $user_id;
        $coupon->approved_at = Carbon::now();
        
        $coupon->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
        
    }

    public function _reject(Request $request){

        // if(!$this->hasAccess('coupon:all:reject')){
        //     return response()->json([
        //         'status'    => 0,
        //         'message'   => 'Access Denied',
        //         'data'      => []
        //     ]);
        // }
        
        $id     = (int) $request->input('id') ?? 0;
        $amount = $request->input('amount');

        $validator = Validator::make($request->all(),[
            'id' => [
                'required',
                'integer'
            ],
            'amount' => [
                'required'
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $coupon = Coupon::find($id);

        if(!$coupon){
            return response()->json([
                'status'    => 0,
                'message'   =>'Error: Record not found',
                'data'      => []
            ]);
        }

        if($coupon->status != 'PEND'){
            return response()->json([
                'status'    => 0,
                'message'   =>'Cannot approve this record, the status status is '.$coupon->status,
                'data'      => []
            ]);
        }

        //Prevents race conditions when approving
        if($coupon->amount != $amount){
            return response()->json([
                'status'    => 0,
                'message'   => 'The record has been altred',
                'data'      => []
            ]);
        }

        
        $user_id = Auth::user()->id;

        $coupon->status      = 'REJC';
        $coupon->approved_by = $user_id;
        $coupon->approved_at = Carbon::now();
        
        $coupon->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }

  

}