<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Section;
use App\Models\DirectMaterialQuantityRequest;
use App\Models\DirectMaterialQuantityRequestItem;
use App\Models\DirectMaterialCanvass;
use App\Models\Supplier;
use App\Models\PaymentTerm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DirectMaterialCanvassController extends Controller
{
    public function list()
    {
        $canViewAll = $this->hasAccess(['material_canvass:all:list', 'direct_material_canvass:all:list']);
        $canViewOwn = $this->hasAccess(['material_canvass:own:list', 'direct_material_canvass:own:list']);

        if (!$canViewAll && !$canViewOwn) {
            return view('access_denied');
        }

        $projects = Project::orderBy('name', 'ASC')->where('status', '=', 'ACTV')->get();

        return view('direct_material_canvass/list', [
            'projects' => $projects
        ]);
    }

    public function _list(Request $request)
    {
        $canViewAll = $this->hasAccess(['material_canvass:all:list', 'direct_material_canvass:all:list']);
        $canViewOwn = $this->hasAccess(['material_canvass:own:list', 'direct_material_canvass:own:list']);

        if (!$canViewAll && !$canViewOwn) {
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

        // Only approved direct material requests can be canvassed
        $dmqr = DirectMaterialQuantityRequest::where('status', '=', 'APRV');

        if (!$canViewAll && $canViewOwn) {
            $dmqr = $dmqr->where('created_by', auth()->user()->id);
        }

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
        $id = (int) $id;
        $dmqr = DirectMaterialQuantityRequest::with([
            'Project',
            'Section',
            'Creator',
            'Items.MaterialItem',
            'Items.Canvasses.Supplier',
            'Items.Canvasses.PaymentTerm'
        ])->findOrFail($id);

        $suppliers     = Supplier::orderBy('name', 'ASC')->whereNull('deleted_at')->get();
        $payment_terms = PaymentTerm::orderBy('text', 'ASC')->get();

        return view('direct_material_canvass/display', [
            'request'       => $dmqr,
            'items'         => $dmqr->Items,
            'suppliers'     => $suppliers,
            'payment_terms' => $payment_terms
        ]);
    }

    public function _create(Request $request)
    {
        $canCreate = $this->hasAccess(['material_canvass:own:create', 'direct_material_canvass:own:create']);
        if (!$canCreate) {
            return response()->json([
                'status'  => 0,
                'message' => 'Access Denied',
                'data'    => []
            ]);
        }

        $validator = Validator::make($request->all(), [
            'direct_material_quantity_request_id'      => ['required', 'integer'],
            'direct_material_quantity_request_item_id' => ['required', 'integer'],
            'supplier_id'                             => ['required', 'integer', 'min:1'],
            'payment_term_id'                         => ['required', 'integer', 'min:1'],
            'price'                                   => ['required', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 0,
                'message' => $validator->errors()->first(),
                'data'    => []
            ]);
        }

        $dmqr_id      = (int) $request->input('direct_material_quantity_request_id');
        $dmqr_item_id = (int) $request->input('direct_material_quantity_request_item_id');

        $dmqr = DirectMaterialQuantityRequest::find($dmqr_id);
        if (!$dmqr || $dmqr->status != 'APRV') {
            return response()->json([
                'status'  => 0,
                'message' => 'Direct Material Request must be approved before adding canvass.',
                'data'    => []
            ]);
        }

        $dmqrItem = DirectMaterialQuantityRequestItem::where('id', $dmqr_item_id)
            ->where('direct_material_quantity_request_id', $dmqr_id)
            ->first();

        if (!$dmqrItem) {
            return response()->json([
                'status'  => 0,
                'message' => 'Direct Material Request Item not found.',
                'data'    => []
            ]);
        }

        $user = auth()->user();

        $canvass = new DirectMaterialCanvass();
        $canvass->direct_material_quantity_request_id      = $dmqr_id;
        $canvass->direct_material_quantity_request_item_id = $dmqr_item_id;
        $canvass->supplier_id                             = (int) $request->input('supplier_id');
        $canvass->payment_term_id                         = (int) $request->input('payment_term_id');
        $canvass->price                                   = (float) $request->input('price');
        $canvass->remarks                                 = $request->input('remarks') ?? null;
        $canvass->status                                  = 'PEND';
        $canvass->created_by                              = $user->id;
        $canvass->save();

        return response()->json([
            'status'  => 1,
            'message' => 'Canvass added successfully.',
            'data'    => [
                'id' => $canvass->id
            ]
        ]);
    }

    public function _delete(Request $request)
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
        $canDeleteAll = $this->hasAccess(['material_canvass:all:delete', 'direct_material_canvass:all:delete']);
        $canDeleteOwn = $this->hasAccess(['material_canvass:own:delete', 'direct_material_canvass:own:delete']);

        if (!$canDeleteAll && (!$canDeleteOwn || $canvass->created_by != $user->id)) {
            return response()->json([
                'status'  => 0,
                'message' => 'Access Denied',
                'data'    => []
            ]);
        }

        if ($canvass->status != 'PEND') {
            return response()->json([
                'status'  => 0,
                'message' => 'Canvass cannot be deleted unless in pending status.',
                'data'    => []
            ]);
        }

        $canvass->deleted_by = $user->id;
        $canvass->delete();

        return response()->json([
            'status'  => 1,
            'message' => 'Canvass deleted successfully.',
            'data'    => []
        ]);
    }
}
