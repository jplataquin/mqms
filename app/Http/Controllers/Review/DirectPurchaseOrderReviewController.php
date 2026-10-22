<?php

namespace App\Http\Controllers\Review;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Section;
use App\Models\Supplier;
use App\Models\PaymentTerm;
use App\Models\DirectPurchaseOrder;
use App\Models\DirectPurchaseOrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DirectPurchaseOrderReviewController extends Controller
{
    public function list()
    {
        if (!$this->hasAccess(['purchase_order:all:view', 'direct_purchase_order:all:view'])) {
            return view('access_denied');
        }

        $projects  = Project::orderBy('name', 'ASC')->where('status', '=', 'ACTV')->get();
        $suppliers = Supplier::orderBy('name', 'ASC')->whereNull('deleted_at')->get();

        return view('review/direct_purchase_order/list', [
            'projects'  => $projects,
            'suppliers' => $suppliers
        ]);
    }

    public function _list(Request $request)
    {
        if (!$this->hasAccess(['purchase_order:all:view', 'direct_purchase_order:all:view'])) {
            return response()->json([
                'status'  => 0,
                'message' => 'Access Denied',
                'data'    => []
            ]);
        }

        $page        = (int) ($request->input('page') ?? 1);
        $limit       = (int) ($request->input('limit') ?? 10);
        $project_id  = (int) ($request->input('project_id') ?? 0);
        $section_id  = (int) ($request->input('section_id') ?? 0);
        $supplier_id = (int) ($request->input('supplier_id') ?? 0);
        $query       = (int) ($request->input('query') ?? 0);
        $status      = $request->input('status') ?? '';
        $orderBy     = $request->input('order_by') ?? 'id';
        $order       = $request->input('order') ?? 'DESC';

        $dpo = new DirectPurchaseOrder();

        if ($status) {
            $dpo = $dpo->where('status', '=', $status);
        } else {
            // Default review queue: pending review or void review
            $dpo = $dpo->where(function ($q) {
                $q->where('status', '=', 'PEND')
                  ->orWhere('status', '=', 'REVO');
            });
        }

        if ($query) {
            $dpo = $dpo->where('id', '=', $query);
        }

        if ($project_id) {
            $dpo = $dpo->where('project_id', '=', $project_id);
            if ($section_id) {
                $dpo = $dpo->where('section_id', '=', $section_id);
            }
        }

        if ($supplier_id) {
            $dpo = $dpo->where('supplier_id', '=', $supplier_id);
        }

        if ($limit > 0) {
            $offset = ($page - 1) * $limit;
            $result = $dpo->orderBy($orderBy, $order)->skip($offset)->take($limit)->with(['Project', 'Section', 'Supplier', 'PaymentTerm', 'User'])->get();
        } else {
            $result = $dpo->orderBy($orderBy, $order)->take($limit)->with(['Project', 'Section', 'Supplier', 'PaymentTerm', 'User'])->get();
        }

        return response()->json([
            'status'  => 1,
            'message' => '',
            'data'    => $result
        ]);
    }

    public function display($id)
    {
        if (!$this->hasAccess(['purchase_order:all:view', 'direct_purchase_order:all:view'])) {
            return view('access_denied');
        }

        $dpo = DirectPurchaseOrder::with(['Items.MaterialItem', 'Supplier', 'PaymentTerm', 'Project', 'Section'])->findOrFail($id);

        $extras = json_decode($dpo->extras) ?? [];

        $dpo_details = [
            "DPO Number"      => str_pad($dpo->id, 6, '0', STR_PAD_LEFT),
            "Supplier"        => $dpo->Supplier ? $dpo->Supplier->name : 'N/A',
            "Payment Term"    => $dpo->PaymentTerm ? $dpo->PaymentTerm->text : 'N/A',
            "Project"         => $dpo->Project ? $dpo->Project->name : 'None',
            "Section"         => $dpo->Section ? $dpo->Section->name : 'None',
            "Date Needed"     => $dpo->date_needed ?: 'None',
            "Status"          => $dpo->status,
            "Created By"      => $dpo->CreatedByUser()->name . ' ' . $dpo->created_at,
        ];

        if ($dpo->updated_at && $dpo->UpdatedByUser()->name) {
            $dpo_details["Updated By"] = $dpo->UpdatedByUser()->name . ' ' . $dpo->updated_at;
        }

        if ($dpo->approved_at && $dpo->ApprovedByUser()->name) {
            $dpo_details["Approved By"] = $dpo->ApprovedByUser()->name . ' ' . $dpo->approved_at;
        }

        if ($dpo->rejected_at && $dpo->RejectedByUser()->name) {
            $dpo_details["Rejected By"] = $dpo->RejectedByUser()->name . ' ' . $dpo->rejected_at;
        }

        if ($dpo->void_at && $dpo->VoidByUser()->name) {
            $dpo_details["Void By"] = $dpo->VoidByUser()->name . ' ' . $dpo->void_at;
        }

        $canApprove    = $this->hasAccess(['purchase_order:all:approve', 'direct_purchase_order:all:approve']);
        $canReject     = $this->hasAccess(['purchase_order:all:reject', 'direct_purchase_order:all:reject']);
        $canVoid       = $this->hasAccess(['purchase_order:all:void', 'direct_purchase_order:all:void']);
        $canRejectVoid = $this->hasAccess(['purchase_order:all:reject_void_request', 'direct_purchase_order:all:reject_void_request']);

        return view('review/direct_purchase_order/display', [
            'direct_purchase_order' => $dpo,
            'supplier'              => $dpo->Supplier,
            'payment_term'          => $dpo->PaymentTerm,
            'items'                 => $dpo->Items,
            'extras'                => $extras,
            'dpo_details'           => $dpo_details,
            'canApprove'            => $canApprove,
            'canReject'             => $canReject,
            'canVoid'               => $canVoid,
            'canRejectVoid'         => $canRejectVoid
        ]);
    }

    public function _approve(Request $request)
    {
        if (!$this->hasAccess(['purchase_order:all:approve', 'direct_purchase_order:all:approve'])) {
            return response()->json([
                'status'  => 0,
                'message' => 'Access Denied',
                'data'    => []
            ]);
        }

        $id = (int) $request->input('id');
        $dpo = DirectPurchaseOrder::find($id);

        if (!$dpo) {
            return response()->json([
                'status'  => 0,
                'message' => 'Record not found',
                'data'    => []
            ]);
        }

        if ($dpo->status != 'PEND') {
            return response()->json([
                'status'  => 0,
                'message' => 'Record cannot be approved, status is not pending',
                'data'    => []
            ]);
        }

        $user_id = Auth::user()->id;

        DB::beginTransaction();

        try {
            $dpo->approved_by = $user_id;
            $dpo->status      = 'APRV';
            $dpo->approved_at = Carbon::now();
            $dpo->save();

            DB::table('direct_purchase_order_items')->where('direct_purchase_order_id', $dpo->id)
                ->update(['status' => 'APRV']);

            DB::commit();

            return response()->json([
                'status'  => 1,
                'message' => 'Direct Purchase Order approved successfully',
                'data'    => [
                    'id' => $dpo->id
                ]
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

    public function _reject(Request $request)
    {
        if (!$this->hasAccess(['purchase_order:all:reject', 'direct_purchase_order:all:reject'])) {
            return response()->json([
                'status'  => 0,
                'message' => 'Access Denied',
                'data'    => []
            ]);
        }

        $id = (int) $request->input('id');
        $dpo = DirectPurchaseOrder::find($id);

        if (!$dpo) {
            return response()->json([
                'status'  => 0,
                'message' => 'Record not found',
                'data'    => []
            ]);
        }

        if ($dpo->status != 'PEND') {
            return response()->json([
                'status'  => 0,
                'message' => 'Record cannot be rejected, status is not pending',
                'data'    => []
            ]);
        }

        $user_id = Auth::user()->id;

        DB::beginTransaction();

        try {
            $dpo->rejected_by = $user_id;
            $dpo->status      = 'REJC';
            $dpo->rejected_at = Carbon::now();
            $dpo->save();

            DB::table('direct_purchase_order_items')->where('direct_purchase_order_id', $dpo->id)
                ->update(['status' => 'REJC']);

            DB::commit();

            return response()->json([
                'status'  => 1,
                'message' => 'Direct Purchase Order rejected successfully',
                'data'    => [
                    'id' => $dpo->id
                ]
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

    public function _void(Request $request)
    {
        if (!$this->hasAccess(['purchase_order:all:void', 'direct_purchase_order:all:void'])) {
            return response()->json([
                'status'  => 0,
                'message' => 'Access Denied',
                'data'    => []
            ]);
        }

        $id = (int) $request->input('id');
        $dpo = DirectPurchaseOrder::find($id);

        if (!$dpo) {
            return response()->json([
                'status'  => 0,
                'message' => 'Record not found',
                'data'    => []
            ]);
        }

        if ($dpo->status != 'APRV' && $dpo->status != 'REVO') {
            return response()->json([
                'status'  => 0,
                'message' => 'Record cannot be voided, status must be approved or void requested',
                'data'    => []
            ]);
        }

        $user_id = Auth::user()->id;

        DB::beginTransaction();

        try {
            $dpo->void_by = $user_id;
            $dpo->status  = 'VOID';
            $dpo->void_at = Carbon::now();
            $dpo->save();

            DB::table('direct_purchase_order_items')->where('direct_purchase_order_id', $dpo->id)
                ->update(['status' => 'VOID']);

            DB::commit();

            return response()->json([
                'status'  => 1,
                'message' => 'Direct Purchase Order voided successfully',
                'data'    => [
                    'id' => $dpo->id
                ]
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
        if (!$this->hasAccess(['purchase_order:all:reject_void_request', 'direct_purchase_order:all:reject_void_request'])) {
            return response()->json([
                'status'  => 0,
                'message' => 'Access Denied',
                'data'    => []
            ]);
        }

        $id = (int) $request->input('id');
        $dpo = DirectPurchaseOrder::find($id);

        if (!$dpo) {
            return response()->json([
                'status'  => 0,
                'message' => 'Record not found',
                'data'    => []
            ]);
        }

        if ($dpo->status != 'REVO') {
            return response()->json([
                'status'  => 0,
                'message' => 'Error: Record status is not REVO',
                'data'    => []
            ]);
        }

        DB::beginTransaction();

        try {
            $dpo->status = 'APRV';
            $dpo->save();

            DB::table('direct_purchase_order_items')->where('direct_purchase_order_id', $dpo->id)
                ->update(['status' => 'APRV']);

            DB::commit();

            return response()->json([
                'status'  => 1,
                'message' => 'Void request rejected',
                'data'    => [
                    'id' => $dpo->id
                ]
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
