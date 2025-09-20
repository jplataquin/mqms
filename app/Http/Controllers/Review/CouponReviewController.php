<?php

namespace App\Http\Controllers\Review;

use Illuminate\Http\Request;
use App\Models\Coupon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Carbon\Carbon;


class CouponReviewController extends Controller
{

    public function list(){
        
        return view('review/coupon/list');
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
                'integer',
                'gte:1'
            ],
            'from' => [
                'date_format:Y-m-d'
            ],
            'to' => [
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
        
        $coupon = $coupon->where('status','PEND');
        
        if($created_by){
            $coupon = $coupon->where('created_by',$created_by);
        }

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
 
        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> $result
        ]);
    }



    public function _approve(Request $request){

        if(!$this->hasAccess('coupon:all:approve')){
            return response()->json([
                'status'    => 0,
                'message'   => 'Access Denied',
                'data'      => []
            ]);
        }

        $id = (int) $request->input('id') ?? 0;

        $validator = Validator::make($request->all(),[
            'id' => [
                'required',
                'integer'
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
                'status' => 0,
                'message'=>'Error: Record not found',
                'data'=> []
            ]);
        }

        if($coupon->status != 'PEND'){
            return response()->json([
                'status' => 0,
                'message'=>'Cannot approve this record, the status status is '.$coupon->status,
                'data'=> []
            ]);
        }

        
        $user_id = Auth::user()->id;

        $coupon->status      = 'APRV';
        $coupon->approved_by = $user_id;
        $coupon->approved_at = Carbon::now();
        
        $coupon->save();

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> []
        ]);
        
    }

    public function _reject(Request $request){

        if(!$this->hasAccess('component:all:reject')){
            return response()->json([
                'status'    => 0,
                'message'   => 'Access Denied',
                'data'      => []
            ]);
        }

        $id = (int) $request->input('id') ?? 0;

        $validator = Validator::make($request->all(),[
            'id' => [
                'required',
                'integer'
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        
        $component = Component::find($id);

        if(!$component){
            return response()->json([
                'status' => 0,
                'message'=>'Record not found',
                'data'=> []
            ]);
        }

        if($component->status != 'PEND' || $component->status == 'APRV'){
            return response()->json([
                'status' => 0,
                'message'=>'Cannot reject this record, it has status '.$component->status,
                'data'=> []
            ]);
        }

        
        $user_id = Auth::user()->id;

        $component->status      = 'REJC';
        $component->rejected_by = $user_id;
        $component->rejected_at = Carbon::now();
        
        $component->save();

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> []
        ]);
    }

    public function _revert_to_pending(Request $request){

        if(!$this->hasAccess('component:all:revert_to_pending')){
            return response()->json([
                'status'    => 0,
                'message'   => 'Access Denied',
                'data'      => []
            ]);
        }

        $id = (int) $request->input('id') ?? 0;

        $validator = Validator::make($request->all(),[
            'id' => [
                'required',
                'integer'
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        
        $component = Component::find($id);

        if(!$component){
            return response()->json([
                'status' => 0,
                'message'=>'Record not found',
                'data'=> []
            ]);
        }

        if($component->status == 'PEND'){
            return response()->json([
                'status' => 0,
                'message'=>'The status for this record is already pending',
                'data'=> []
            ]);
        }

        
        $user_id = Auth::user()->id;

        $component->status      = 'PEND';
        $component->updated_by  = $user_id;
        $component->updated_at  = Carbon::now();
        
        $component->save();

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> []
        ]);
    }

}