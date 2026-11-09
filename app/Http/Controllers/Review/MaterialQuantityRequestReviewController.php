<?php

namespace App\Http\Controllers\Review;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Section;
use App\Models\Component;
use App\Models\Unit;
use App\Models\MaterialQuantityRequest;
use App\Models\MaterialQuantityRequestItem;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class MaterialQuantityRequestReviewController extends Controller
{


    public function display($id){

        
        $id = (int) $id;

        $materialQuantityRequest = MaterialQuantityRequest::findOrFail($id);

        $this->requireAccess('material_request:all:view');

        $project         = $materialQuantityRequest->Project;
        $section         = $materialQuantityRequest->Section;
        $contract_item   = $materialQuantityRequest->ContractItem;
        $component       = $materialQuantityRequest->Component;
        $request_items   = $materialQuantityRequest->Items;

        $unit_options               = Unit::toOptions();
        $component_item_ids         = [];
        $component_item_options     = [];

        foreach($component->ComponentItems as $componentItem){
            $component_item_ids[] = $componentItem->id;

            $component_item_options[$componentItem->id] = [
                'value'     => $componentItem->id,
                'text'      => $componentItem->name,
                'unit_text' => $unit_options[$componentItem->unit_id]->text,
                'unit_id'   => $componentItem->unit_id,
                'quantity'  => $componentItem->quantity
            ];
        }
        
        $material_item_result = DB::table('material_quantities')->whereIn('component_item_id',$component_item_ids)
        ->join('material_items','material_quantities.material_item_id','=','material_items.id')
        ->get();

        $material_options = [];

        foreach($material_item_result as $row){

            if(!isset($material_options[$row->component_item_id])){
                $material_options[$row->component_item_id] = [];
            }

            $material_options[$row->component_item_id][$row->id] = [
                'value'         => $row->material_item_id,
                'text'          => trim($row->name.' '.$row->specification_unit_packaging.' '.$row->brand),
                'equivalent'    => $row->equivalent,
                'budget'        => $component_item_options[$row->component_item_id]['quantity'],
                'unit_text'     => $component_item_options[$row->component_item_id]['unit_text']
            ];
        }

        $unit_options = Unit::toOptions();
        
        $canVoidAll = $this->hasAccess(['material_request:all:void']);
        $canVoidOwn = $this->hasAccess(['material_request:own:void']);
        $canVoid    = $canVoidAll || ($canVoidOwn && $materialQuantityRequest->created_by == Auth::user()->id);

        return view('review/material_quantity_request/display',[
            'project'                   => $project,
            'section'                   => $section,
            'contract_item'             => $contract_item,
            'component'                 => $component,
            'material_quantity_request' => $materialQuantityRequest,
            'request_items'             => $request_items,
            'material_options'          => $material_options,
            'componentItem_options'     => $component_item_options,
            'unit_options'              => $unit_options,
            'canVoid'                   => $canVoid
        ]);
    }

    public function list(){

        $projects = Project::orderBy('name','ASC')->where('status','=','ACTV')->get();

        return view('review/material_quantity_request/list',[
            'projects' => $projects
        ]);
    }

 
 
    public function _list(Request $request){

        //todo check role

        $page       = (int) $request->input('page')     ?? 1;
        $limit      = (int) $request->input('limit')    ?? 10;
        $project_id = (int) $request->input('project_id')  ?? 0;
        $section_id      = (int) $request->input('section_id')  ?? 0;
        $component_id    = (int) $request->input('component_id')  ?? 0;
        $query      = (int) $request->input('query')    ?? 0;
        $status     = $request->input('status')         ?? '';
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $result = [];

        $materialQuantityRequest = new MaterialQuantityRequest();

        if ($status) {
            $materialQuantityRequest = $materialQuantityRequest->where('status', '=', $status);
        } else {
            $materialQuantityRequest = $materialQuantityRequest->where(function($q) {
                $q->where('status', '=', 'PEND')
                  ->orWhere('status', '=', 'REVO');
            });
        }
        
        if($query){
            $materialQuantityRequest = $materialQuantityRequest->where('id','=',$query);
        }

        if($project_id){
            
            $materialQuantityRequest = $materialQuantityRequest->where('project_id','=',$project_id);

            if($section_id){
                $materialQuantityRequest = $materialQuantityRequest->where('section_id','=',$section_id);

                if($component_id){
                    $materialQuantityRequest = $materialQuantityRequest->where('component_id','=',$component_id);

                }
            }
        }

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $materialQuantityRequest->orderBy($orderBy,$order)->skip($page)->take($limit)->with('Project')->with('Section')->with('Component')->with('User')->get();
            
        }else{

            $result = $materialQuantityRequest->orderBy($orderBy,$order)->take($limit)->with('Project')->with('Section')->with('Component')->with('User')->get();
        }

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> $result
        ]);
    }
    
    public function qr_approve($id,$hash_code){

        $id = (int) $id;

        
        if(!$hash_code){
            return view('review/material_quantity_request/qr/error',[
                'message' => 'Hash code not found',
                'data'    => [],
                'id'      => $id
            ]);
        }

        if(!$this->hasAccess('material_request:all:approve')){
            return view('review/material_quantity_request/qr/access_denied',[
                'id'            => $id,
                'missing_codes' => ['material_request:all:approve']
            ]);
        }


        $material_quantity_request = MaterialQuantityRequest::find($id);
        
        if(!$material_quantity_request){
            return view('review/material_quantity_request/qr/error',[
                'message' => 'Record not found',
                'data'    => [],
                'id'      => $id
            ]);
        }

        $current_hash_code = $material_quantity_request->getHashCode();

        if($hash_code != $current_hash_code){
            return view('review/material_quantity_request/qr/error',[
                'message' => 'The printed copy is not updated',
                'data'    => [],
                'id'      => $id
            ]);
        }

        if($material_quantity_request->status != 'PEND'){
           return view('review/material_quantity_request/qr/error',[
                'message' => 'The record is no longer in pending status',
                'data'    => [],
                'id'      => $id
            ]);
        }


        $user_id = Auth::user()->id;

        DB::beginTransaction();

        try {


            $material_quantity_request->status        = 'APRV';
            $material_quantity_request->approved_by   = $user_id;
            $material_quantity_request->approved_at   = Carbon::now();
            $material_quantity_request->save();

            $affected = DB::table('material_quantity_request_items')
                ->where('material_quantity_request_id', $id)
                ->update(['status' => 'APRV']);

            DB::commit();

            return view('review/material_quantity_request/qr/approve',[
                'id'         => $id,
                'hash_code'  => $hash_code 
            ]);

        }catch(\Exception $e){

            return view('review/material_quantity_request/qr/error',[
                'message' => 'Record update failed',
                'data'    => [$e->getMessage()],
                'id'      => $id
            ]);

            DB::rollback();

            return false;
            
         }
    }

    public function _approve(Request $request){

        $this->requireAccess('material_request:all:approve');

        $id = (int) $request->input('id');

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

        $material_quantity_request = MaterialQuantityRequest::find($id);

        if(!$material_quantity_request){
            return response()->json([
                'status'    => 0,
                'message'   => 'No record found',
                'data'      => []
            ]);
        }

        if($material_quantity_request->status != 'PEND'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record is no longer in Pending status',
                'data'      => []
            ]);
        }


        $user_id = Auth::user()->id;

        DB::beginTransaction();

        try {


            $material_quantity_request->status        = 'APRV';
            $material_quantity_request->approved_by   = $user_id;
            $material_quantity_request->approved_at   = Carbon::now();
            $material_quantity_request->save();

            $affected = DB::table('material_quantity_request_items')
                ->where('material_quantity_request_id', $id)
                ->update(['status' => 'APRV']);

            DB::commit();

            return response()->json([
                'status'    => 1,
                'message'   => '',
                'data'      => []
            ]);

        }catch(\Exception $e){

            return response()->json([
                'status'    => 0,
                'message'   => 'Record update failed',
                'data'      => $e->getMessage()
            ]);

            DB::rollback();

            return false;
            
         }
    }


    public function _reject(Request $request){

        $this->requireAccess('material_request:all:reject');

        $id = (int) $request->input('id');

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

        $materialQuantityRequest = MaterialQuantityRequest::find($id);

        if(!$materialQuantityRequest){
            return response()->json([
                'status'    => 0,
                'message'   => 'No record found',
                'data'      => []
            ]);
        }

        if($materialQuantityRequest->status != 'PEND'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record is no longer in Pending status',
                'data'      => []
            ]);
        }

        $user_id = Auth::user()->id;

        DB::beginTransaction();

        try {

            $mytime = Carbon::now();

            $materialQuantityRequest->status           = 'REJC';
            $materialQuantityRequest->rejected_by   = $user_id;
            $materialQuantityRequest->rejected_at   = $mytime->toDateTimeString();
            $materialQuantityRequest->save();

            $affected = DB::table('material_quantity_request_items')
                ->where('material_quantity_request_id', $id)
                ->update(['status' => 'REJC']);

            DB::commit();

            return response()->json([
                'status'    => 1,
                'message'   => '',
                'data'      => []
            ]);

        }catch(\Exception $e){

            return response()->json([
                'status'    => 0,
                'message'   => 'Record update failed',
                'data'      => $e->getMessage()
            ]);

            DB::rollback();

            return false;
            
         }
    }

    public function _void(Request $request)
    {
        $id = (int) $request->input('id');
        $material_quantity_request = MaterialQuantityRequest::find($id);

        if (!$material_quantity_request) {
            return response()->json([
                'status'  => 0,
                'message' => 'Record not found',
                'data'    => []
            ]);
        }

        $user = auth()->user();
        $canVoidAll = $this->hasAccess(['material_request:all:void']);
        $canVoidOwn = $this->hasAccess(['material_request:own:void']);

        if (!$canVoidAll) {
            if (!$canVoidOwn || $material_quantity_request->created_by != $user->id) {
                return response()->json([
                    'status'  => 0,
                    'message' => 'Access Denied',
                    'data'    => []
                ]);
            }
        }

        if (!in_array($material_quantity_request->status, ['APRV', 'CLSD', 'REVO'])) {
            return response()->json([
                'status'  => 0,
                'message' => 'Record cannot be voided, status must be approved, closed, or request void',
                'data'    => []
            ]);
        }

        // Check if any non-void purchase orders exist
        $activePoCount = PurchaseOrder::where('material_quantity_request_id', $material_quantity_request->id)
            ->where('status', '!=', 'VOID')
            ->count();

        if ($activePoCount > 0) {
            return response()->json([
                'status'  => 0,
                'message' => 'Cannot void request: Existing purchase orders must first be voided before the material quantity request can be set to status void.',
                'data'    => []
            ]);
        }

        DB::beginTransaction();

        try {
            $material_quantity_request->void_by = $user->id;
            $material_quantity_request->void_at = Carbon::now();
            $material_quantity_request->status  = 'VOID';
            $material_quantity_request->save();

            DB::table('material_quantity_request_items')
                ->where('material_quantity_request_id', $material_quantity_request->id)
                ->update(['status' => 'VOID']);

            DB::commit();

            return response()->json([
                'status'  => 1,
                'message' => 'Material Quantity Request voided successfully',
                'data'    => ['id' => $material_quantity_request->id]
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status'  => 0,
                'message' => $e->getMessage(),
                'data'    => []
            ]);
        }
    }

    public function _reject_void(Request $request)
    {
        $id = (int) $request->input('id');
        $material_quantity_request = MaterialQuantityRequest::find($id);

        if (!$material_quantity_request) {
            return response()->json([
                'status'  => 0,
                'message' => 'Record not found',
                'data'    => []
            ]);
        }

        $user = auth()->user();
        $canVoidAll = $this->hasAccess(['material_request:all:void']);
        $canVoidOwn = $this->hasAccess(['material_request:own:void']);

        if (!$canVoidAll) {
            if (!$canVoidOwn || $material_quantity_request->created_by != $user->id) {
                return response()->json([
                    'status'  => 0,
                    'message' => 'Access Denied',
                    'data'    => []
                ]);
            }
        }

        if ($material_quantity_request->status != 'REVO') {
            return response()->json([
                'status'  => 0,
                'message' => 'Error: Record status is not REVO',
                'data'    => []
            ]);
        }

        DB::beginTransaction();

        try {
            $prevStatus = in_array($material_quantity_request->previous_status, ['APRV', 'CLSD']) 
                ? $material_quantity_request->previous_status 
                : 'APRV';

            $material_quantity_request->status          = $prevStatus;
            $material_quantity_request->previous_status = null;
            $material_quantity_request->save();

            DB::commit();

            return response()->json([
                'status'  => 1,
                'message' => 'Void request rejected successfully',
                'data'    => ['id' => $material_quantity_request->id]
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status'  => 0,
                'message' => $e->getMessage(),
                'data'    => []
            ]);
        }
    }
} 