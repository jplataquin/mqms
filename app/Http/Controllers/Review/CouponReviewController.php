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

        $coupon = $coupon->where('status','PEND');
        

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