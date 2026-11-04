<?php

namespace App\Http\Controllers\Review;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\DirectMaterialQuantityRequest;
use App\Models\DirectMaterialQuantityRequestItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DirectMaterialQuantityRequestReviewController extends Controller
{
    public function list()
    {
        $this->requireAccess(['material_request:all:view', 'direct_material_quantity_request:all:view']);

        $projects = Project::orderBy('name', 'ASC')->where('status', '=', 'ACTV')->get();

        return view('review/direct_material_quantity_request/list', [
            'projects' => $projects
        ]);
    }

    public function _list(Request $request)
    {
        $this->requireAccess(['material_request:all:view', 'direct_material_quantity_request:all:view']);

        $page        = (int) ($request->input('page') ?? 1);
        $limit       = (int) ($request->input('limit') ?? 10);
        $project_id  = (int) ($request->input('project_id') ?? 0);
        $section_id  = (int) ($request->input('section_id') ?? 0);
        $query       = (int) ($request->input('query') ?? 0);
        $status      = $request->input('status') ?? 'PEND';
        $orderBy     = $request->input('order_by') ?? 'id';
        $order       = $request->input('order') ?? 'DESC';

        $dmqr = new DirectMaterialQuantityRequest();

        if ($query) {
            $dmqr = $dmqr->where('id', '=', $query);
        }

        if ($status) {
            $dmqr = $dmqr->where('status', '=', $status);
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
        $this->requireAccess(['material_request:all:view', 'direct_material_quantity_request:all:view']);

        $id = (int) $id;
        $dmqr = DirectMaterialQuantityRequest::with(['Project', 'Section', 'Creator', 'Items.MaterialItem'])->findOrFail($id);

        $canApprove = $this->hasAccess(['material_request:all:approve', 'direct_material_quantity_request:all:approve']);
        $canReject  = $this->hasAccess(['material_request:all:reject', 'direct_material_quantity_request:all:reject']);

        return view('review/direct_material_quantity_request/display', [
            'request'    => $dmqr,
            'items'      => $dmqr->Items,
            'canApprove' => $canApprove,
            'canReject'  => $canReject
        ]);
    }

    public function _approve(Request $request)
    {
        $this->requireAccess(['material_request:all:approve', 'direct_material_quantity_request:all:approve']);

        $id = (int) $request->input('id');
        $dmqr = DirectMaterialQuantityRequest::find($id);

        if (!$dmqr) {
            return response()->json([
                'status'  => 0,
                'message' => 'Record not found',
                'data'    => []
            ]);
        }

        if ($dmqr->status != 'PEND') {
            return response()->json([
                'status'  => 0,
                'message' => 'Record is not in pending status.',
                'data'    => []
            ]);
        }

        $user = auth()->user();

        DB::beginTransaction();
        try {
            $dmqr->status      = 'APRV';
            $dmqr->approved_by = $user->id;
            $dmqr->approved_at = Carbon::now();
            $dmqr->save();

            DirectMaterialQuantityRequestItem::where('direct_material_quantity_request_id', $id)
                ->update(['status' => 'APRV']);

            DB::commit();

            return response()->json([
                'status'  => 1,
                'message' => 'Direct Material Request approved successfully.',
                'data'    => []
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
        $this->requireAccess(['material_request:all:reject', 'direct_material_quantity_request:all:reject']);

        $id = (int) $request->input('id');
        $dmqr = DirectMaterialQuantityRequest::find($id);

        if (!$dmqr) {
            return response()->json([
                'status'  => 0,
                'message' => 'Record not found',
                'data'    => []
            ]);
        }

        if ($dmqr->status != 'PEND') {
            return response()->json([
                'status'  => 0,
                'message' => 'Record is not in pending status.',
                'data'    => []
            ]);
        }

        $user = auth()->user();
        $dmqr->status      = 'DPRV';
        $dmqr->rejected_by = $user->id;
        $dmqr->rejected_at = Carbon::now();
        $dmqr->save();

        return response()->json([
            'status'  => 1,
            'message' => 'Direct Material Request rejected successfully.',
            'data'    => []
        ]);
    }

    public function qr_approve($id, $hash_code = null)
    {
        $id = (int) $id;

        if (!$hash_code) {
            return view('review/direct_material_quantity_request/qr/error', [
                'message' => 'Hash code not found',
                'data'    => [],
                'id'      => $id
            ]);
        }

        $approveCodes = ['material_request:all:approve', 'direct_material_quantity_request:all:approve'];
        if (!$this->hasAccess($approveCodes)) {
            return view('review/direct_material_quantity_request/qr/access_denied', [
                'id'            => $id,
                'missing_codes' => $approveCodes
            ]);
        }

        $dmqr = DirectMaterialQuantityRequest::find($id);

        if (!$dmqr) {
            return view('review/direct_material_quantity_request/qr/error', [
                'message' => 'Record not found',
                'data'    => [],
                'id'      => $id
            ]);
        }

        $current_hash_code = $dmqr->getHashCode();

        if ($hash_code != $current_hash_code) {
            return view('review/direct_material_quantity_request/qr/error', [
                'message' => 'The printed copy is not updated',
                'data'    => [],
                'id'      => $id
            ]);
        }

        if ($dmqr->status != 'PEND') {
            return view('review/direct_material_quantity_request/qr/error', [
                'message' => 'The record is no longer in pending status',
                'data'    => [],
                'id'      => $id
            ]);
        }

        $user_id = Auth::user()->id;

        DB::beginTransaction();

        try {
            $dmqr->status      = 'APRV';
            $dmqr->approved_by = $user_id;
            $dmqr->approved_at = Carbon::now();
            $dmqr->save();

            DirectMaterialQuantityRequestItem::where('direct_material_quantity_request_id', $id)
                ->update(['status' => 'APRV']);

            DB::commit();

            return view('review/direct_material_quantity_request/qr/approve', [
                'id'        => $id,
                'hash_code' => $hash_code
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return view('review/direct_material_quantity_request/qr/error', [
                'message' => 'Record update failed',
                'data'    => [$e->getMessage()],
                'id'      => $id
            ]);
        }
    }
}
