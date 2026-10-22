<?php

namespace App\Http\Controllers\Review;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\DirectMaterialQuantityRequest;
use App\Models\DirectMaterialCanvass;
use App\Models\DirectPurchaseOrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DirectMaterialCanvassReviewController extends Controller
{
    public function list()
    {
        if (!$this->hasAccess(['material_canvass:all:view', 'direct_material_canvass:all:view'])) {
            return view('access_denied');
        }

        $projects = Project::orderBy('name', 'ASC')->where('status', '=', 'ACTV')->get();

        return view('review/direct_material_canvass/list', [
            'projects' => $projects
        ]);
    }

    public function _list(Request $request)
    {
        if (!$this->hasAccess(['material_canvass:all:view', 'direct_material_canvass:all:view'])) {
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
        $query       = (int) ($request->input('query') ?? 0);
        $orderBy     = $request->input('order_by') ?? 'id';
        $order       = $request->input('order') ?? 'DESC';

        // Requests that are approved and have pending canvasses
        $dmqr = DirectMaterialQuantityRequest::where('status', '=', 'APRV')
            ->whereIn('id', function ($q) {
                $q->select('direct_material_quantity_request_id')
                    ->from('direct_material_canvass')
                    ->where('status', '=', 'PEND')
                    ->whereNull('deleted_at');
            });

        if ($query) {
            $dmqr = $dmqr->where('id', '=', $query);
        }

        if ($project_id) {
            $dmqr = $dmqr->where('project_id', '=', $project_id);
            if ($section_id) {
                $dmqr = $dmqr->where('section_id', '=', $section_id);
            }
        }

        if ($limit > 0) {
            $skip = ($page - 1) * $limit;
            $result = $dmqr->orderBy($orderBy, $order)->skip($skip)->take($limit)->with(['Project', 'Section', 'Creator'])->get();
        } else {
            $result = $dmqr->orderBy($orderBy, $order)->take($limit)->with(['Project', 'Section', 'Creator'])->get();
        }

        return response()->json([
            'status'  => 1,
            'message' => '',
            'data'    => $result
        ]);
    }

    public function display($id)
    {
        if (!$this->hasAccess(['material_canvass:all:view', 'direct_material_canvass:all:view'])) {
            return view('access_denied');
        }

        $id = (int) $id;
        $dmqr = DirectMaterialQuantityRequest::with([
            'Project',
            'Section',
            'Creator',
            'Items.MaterialItem',
            'Items.Canvasses.Supplier',
            'Items.Canvasses.PaymentTerm'
        ])->findOrFail($id);

        $canApprove = $this->hasAccess(['material_canvass:all:approve', 'direct_material_canvass:all:approve']);
        $canReject  = $this->hasAccess(['material_canvass:all:reject', 'direct_material_canvass:all:reject']);
        $canVoid    = $this->hasAccess([
            'material_canvass:all:void', 'direct_material_canvass:all:void',
            'material_canvass:all:approve', 'direct_material_canvass:all:approve',
            'material_canvass:own:void', 'direct_material_canvass:own:void',
            'material_canvass:own:create', 'direct_material_canvass:own:create',
            'material_canvass:all:create', 'direct_material_canvass:all:create',
            'material_canvass:all:list', 'direct_material_canvass:all:list',
            'material_canvass:own:list', 'direct_material_canvass:own:list',
            'material_canvass:all:view', 'direct_material_canvass:all:view',
        ]) || $dmqr->created_by == auth()->user()->id;

        return view('review/direct_material_canvass/display', [
            'request'    => $dmqr,
            'items'      => $dmqr->Items,
            'canApprove' => $canApprove,
            'canReject'  => $canReject,
            'canVoid'    => $canVoid
        ]);
    }

    public function _approve(Request $request)
    {
        if (!$this->hasAccess(['material_canvass:all:approve', 'direct_material_canvass:all:approve'])) {
            return response()->json([
                'status'  => 0,
                'message' => 'Access Denied',
                'data'    => []
            ]);
        }

        $id = (int) $request->input('id');
        $canvass = DirectMaterialCanvass::find($id);

        if (!$canvass) {
            return response()->json([
                'status'  => 0,
                'message' => 'Record not found',
                'data'    => []
            ]);
        }

        if ($canvass->status != 'PEND') {
            return response()->json([
                'status'  => 0,
                'message' => 'Canvass is not in pending status.',
                'data'    => []
            ]);
        }

        $user = auth()->user();

        $canvass->status      = 'APRV';
        $canvass->approved_by = $user->id;
        $canvass->approved_at = Carbon::now();
        $canvass->save();

        return response()->json([
            'status'  => 1,
            'message' => 'Canvass approved successfully.',
            'data'    => []
        ]);
    }

    public function _reject(Request $request)
    {
        if (!$this->hasAccess(['material_canvass:all:reject', 'direct_material_canvass:all:reject'])) {
            return response()->json([
                'status'  => 0,
                'message' => 'Access Denied',
                'data'    => []
            ]);
        }

        $id = (int) $request->input('id');
        $canvass = DirectMaterialCanvass::find($id);

        if (!$canvass) {
            return response()->json([
                'status'  => 0,
                'message' => 'Record not found',
                'data'    => []
            ]);
        }

        if ($canvass->status != 'PEND') {
            return response()->json([
                'status'  => 0,
                'message' => 'Canvass is not in pending status.',
                'data'    => []
            ]);
        }

        $user = auth()->user();
        $canvass->status      = 'DPRV';
        $canvass->rejected_by = $user->id;
        $canvass->rejected_at = Carbon::now();
        $canvass->save();

        return response()->json([
            'status'  => 1,
            'message' => 'Canvass rejected successfully.',
            'data'    => []
        ]);
    }

    public function _void(Request $request)
    {
        $id = (int) $request->input('id');
        $canvass = DirectMaterialCanvass::find($id);

        if (!$canvass) {
            return response()->json([
                'status'  => 0,
                'message' => 'Record not found',
                'data'    => []
            ]);
        }

        $user = auth()->user();
        $canVoid = $this->hasAccess([
            'material_canvass:all:void', 'direct_material_canvass:all:void',
            'material_canvass:all:approve', 'direct_material_canvass:all:approve',
            'material_canvass:own:void', 'direct_material_canvass:own:void',
            'material_canvass:own:create', 'direct_material_canvass:own:create',
            'material_canvass:all:create', 'direct_material_canvass:all:create',
            'material_canvass:all:list', 'direct_material_canvass:all:list',
            'material_canvass:own:list', 'direct_material_canvass:own:list',
            'material_canvass:all:view', 'direct_material_canvass:all:view',
        ]) || $canvass->created_by == $user->id || ($canvass->Request && $canvass->Request->created_by == $user->id);

        if (!$canVoid) {
            return response()->json([
                'status'  => 0,
                'message' => 'Access Denied',
                'data'    => []
            ]);
        }

        if ($canvass->status != 'APRV') {
            return response()->json([
                'status'  => 0,
                'message' => 'Canvass cannot be voided unless in approved status.',
                'data'    => []
            ]);
        }

        // Check if there are active Direct Purchase Orders dependent on this canvass
        $hasPo = DirectPurchaseOrderItem::where('direct_material_canvass_id', $canvass->id)
            ->whereHas('DirectPurchaseOrder', function ($q) {
                $q->where('status', '!=', 'VOID');
            })
            ->exists();

        if ($hasPo) {
            return response()->json([
                'status'  => 0,
                'message' => 'Record dependency issue, this canvass has active Direct Purchase Order(s) that are dependent.',
                'data'    => []
            ]);
        }

        $canvass->status  = 'VOID';
        $canvass->void_by = $user->id;
        $canvass->void_at = Carbon::now();
        $canvass->save();

        return response()->json([
            'status'  => 1,
            'message' => 'Canvass voided successfully.',
            'data'    => []
        ]);
    }
}
