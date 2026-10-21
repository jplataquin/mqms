<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Section;
use App\Models\MaterialItem;
use App\Models\DirectMaterialQuantityRequest;
use App\Models\DirectMaterialQuantityRequestItem;
use App\Models\DirectMaterialCanvass;
use App\Models\DirectPurchaseOrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DirectMaterialQuantityRequestController extends Controller
{
    public function list()
    {
        $canViewAll = $this->hasAccess(['material_request:all:list', 'direct_material_quantity_request:all:list']);
        $canViewOwn = $this->hasAccess(['material_request:own:list', 'direct_material_quantity_request:own:list']);

        if (!$canViewAll && !$canViewOwn) {
            return view('access_denied');
        }

        $projects = Project::orderBy('name', 'ASC')->where('status', '=', 'ACTV')->get();
        $material_items = MaterialItem::orderBy('name', 'ASC')->get();

        return view('direct_material_quantity_request/list', [
            'projects'       => $projects,
            'material_items' => $material_items
        ]);
    }

    public function _list(Request $request)
    {
        $page        = (int) ($request->input('page') ?? 1);
        $limit       = (int) ($request->input('limit') ?? 10);
        $project_id  = (int) ($request->input('project_id') ?? 0);
        $section_id  = (int) ($request->input('section_id') ?? 0);
        $query       = (int) ($request->input('query') ?? 0);
        $status      = $request->input('status') ?? '';
        $orderBy     = $request->input('order_by') ?? 'id';
        $order       = $request->input('order') ?? 'DESC';

        $canViewAll = $this->hasAccess(['material_request:all:list', 'direct_material_quantity_request:all:list']);
        $canViewOwn = $this->hasAccess(['material_request:own:list', 'direct_material_quantity_request:own:list']);

        if (!$canViewAll && !$canViewOwn) {
            return response()->json([
                'status'  => 0,
                'message' => 'Access Denied',
                'data'    => []
            ]);
        }

        $dmqr = new DirectMaterialQuantityRequest();

        if (!$canViewAll && $canViewOwn) {
            $dmqr = $dmqr->where('created_by', auth()->user()->id);
        }

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

    public function create()
    {
        if (!$this->hasAccess(['material_request:own:create', 'direct_material_quantity_request:own:create'])) {
            return view('access_denied');
        }

        $projects       = Project::orderBy('name', 'ASC')->where('status', '=', 'ACTV')->get();
        $material_items = MaterialItem::orderBy('name', 'ASC')->get();

        return view('direct_material_quantity_request/create', [
            'projects'       => $projects,
            'material_items' => $material_items
        ]);
    }

    public function _create(Request $request)
    {
        if (!$this->hasAccess(['material_request:own:create', 'direct_material_quantity_request:own:create'])) {
            return response()->json([
                'status'  => 0,
                'message' => 'Access Denied',
                'data'    => []
            ]);
        }

        $raw_project_id  = $request->input('project_id');
        $project_id      = (!empty($raw_project_id) && $raw_project_id !== 'null' && $raw_project_id !== 'undefined' && is_numeric($raw_project_id))
            ? (int) $raw_project_id
            : null;

        $raw_section_id  = $request->input('section_id');
        $section_id      = (!empty($raw_section_id) && $raw_section_id !== 'null' && $raw_section_id !== 'undefined' && is_numeric($raw_section_id) && (int) $raw_section_id > 0)
            ? (int) $raw_section_id
            : null;

        $description     = $request->input('description') ?? '';

        try {
            $items = json_decode($request->input('items'), true);

            if (is_null($items) || !is_array($items) || count($items) === 0) {
                return response()->json([
                    'status'  => 0,
                    'message' => 'At least one item is required to make a Direct Material Request.',
                    'data'    => []
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 0,
                'message' => 'Invalid items JSON payload',
                'data'    => []
            ]);
        }

        $validator = Validator::make([
            'project_id' => $project_id,
            'items'      => $items,
        ], [
            'project_id'         => ['required', 'integer', 'min:1'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.material_item_id' => ['required', 'integer'],
            'items.*.quantity'   => ['required', 'numeric', 'min:0.01'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 0,
                'message' => $validator->errors()->first(),
                'data'    => []
            ]);
        }

        DB::beginTransaction();

        try {
            $user = auth()->user();

            $dmqr = new DirectMaterialQuantityRequest();
            $dmqr->project_id   = $project_id;
            $dmqr->section_id   = $section_id;
            $dmqr->description  = $description;
            $dmqr->status       = 'PEND';
            $dmqr->created_by   = $user->id;
            $dmqr->save();

            foreach ($items as $item) {
                $dmqrItem = new DirectMaterialQuantityRequestItem();
                $dmqrItem->direct_material_quantity_request_id = $dmqr->id;
                $dmqrItem->material_item_id                   = $item['material_item_id'];
                $dmqrItem->quantity                           = $item['quantity'];
                $dmqrItem->remarks                            = $item['remarks'] ?? null;
                $dmqrItem->status                             = 'PEND';
                $dmqrItem->save();
            }

            DB::commit();

            return response()->json([
                'status'  => 1,
                'message' => 'Direct Material Request created successfully.',
                'data'    => [
                    'id' => $dmqr->id
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

    public function display($id)
    {
        $id = (int) $id;
        $dmqr = DirectMaterialQuantityRequest::with(['Project', 'Section', 'Creator', 'Items.MaterialItem'])->findOrFail($id);

        $user = auth()->user();
        $canViewAll = $this->hasAccess(['material_request:all:view', 'direct_material_quantity_request:all:view']);
        $canViewOwn = $this->hasAccess(['material_request:own:view', 'direct_material_quantity_request:own:view']);

        if (!$canViewAll) {
            if (!$canViewOwn || $dmqr->created_by != $user->id) {
                return view('access_denied');
            }
        }

        $canUpdateAll = $this->hasAccess(['material_request:all:update', 'direct_material_quantity_request:all:update']);
        $canUpdateOwn = $this->hasAccess(['material_request:own:update', 'direct_material_quantity_request:own:update']);
        $canEdit = $canUpdateAll || ($canUpdateOwn && $dmqr->created_by == $user->id);

        $canDeleteAll = $this->hasAccess(['material_request:all:delete', 'direct_material_quantity_request:all:delete']);
        $canDeleteOwn = $this->hasAccess(['material_request:own:delete', 'direct_material_quantity_request:own:delete']);
        $canDelete = $canDeleteAll || ($canDeleteOwn && $dmqr->created_by == $user->id);

        return view('direct_material_quantity_request/display', [
            'request'   => $dmqr,
            'items'     => $dmqr->Items,
            'canEdit'   => $canEdit,
            'canDelete' => $canDelete
        ]);
    }

    public function _submit_for_review(Request $request)
    {
        $id = (int) $request->input('id');
        $dmqr = DirectMaterialQuantityRequest::find($id);

        if (!$dmqr) {
            return response()->json([
                'status'  => 0,
                'message' => 'Record not found',
                'data'    => []
            ]);
        }

        $user = auth()->user();
        $canUpdateAll = $this->hasAccess(['material_request:all:update', 'direct_material_quantity_request:all:update']);
        $canUpdateOwn = $this->hasAccess(['material_request:own:update', 'direct_material_quantity_request:own:update']);

        if (!$canUpdateAll && (!$canUpdateOwn || $dmqr->created_by != $user->id)) {
            return response()->json([
                'status'  => 0,
                'message' => 'Access Denied',
                'data'    => []
            ]);
        }

        if ($dmqr->status != 'DRFT') {
            return response()->json([
                'status'  => 0,
                'message' => 'Record is not in draft status.',
                'data'    => []
            ]);
        }

        $dmqr->status     = 'PEND';
        $dmqr->updated_by = $user->id;
        $dmqr->save();

        return response()->json([
            'status'  => 1,
            'message' => 'Direct Material Request submitted for review.',
            'data'    => []
        ]);
    }

    public function _delete(Request $request)
    {
        $id = (int) $request->input('id');
        $dmqr = DirectMaterialQuantityRequest::find($id);

        if (!$dmqr) {
            return response()->json([
                'status'  => 0,
                'message' => 'Record not found',
                'data'    => []
            ]);
        }

        $user = auth()->user();
        $canDeleteAll = $this->hasAccess(['material_request:all:delete', 'direct_material_quantity_request:all:delete']);
        $canDeleteOwn = $this->hasAccess(['material_request:own:delete', 'direct_material_quantity_request:own:delete']);

        if (!$canDeleteAll && (!$canDeleteOwn || $dmqr->created_by != $user->id)) {
            return response()->json([
                'status'  => 0,
                'message' => 'Access Denied',
                'data'    => []
            ]);
        }

        if ($dmqr->status != 'PEND' && $dmqr->status != 'DRFT') {
            return response()->json([
                'status'  => 0,
                'message' => 'Record cannot be deleted, status is not pending or draft.',
                'data'    => []
            ]);
        }

        DB::beginTransaction();

        try {
            $dmqr->forceDelete();
            DirectMaterialQuantityRequestItem::where('direct_material_quantity_request_id', $id)->delete();
            DB::commit();

            return response()->json([
                'status'  => 1,
                'message' => 'Direct Material Request deleted successfully.',
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

    public function print($id)
    {
        $id = (int) $id;
        $dmqr = DirectMaterialQuantityRequest::with(['Project', 'Section', 'Creator', 'Items.MaterialItem'])->findOrFail($id);

        $user = auth()->user();
        $canViewAll = $this->hasAccess(['material_request:all:view', 'direct_material_quantity_request:all:view']);
        $canViewOwn = $this->hasAccess(['material_request:own:view', 'direct_material_quantity_request:own:view']);

        if (!$canViewAll && (!$canViewOwn || $dmqr->created_by != $user->id)) {
            return view('access_denied');
        }

        $hash_code = $dmqr->getHashCode();

        return view('direct_material_quantity_request/print', [
            'request'          => $dmqr,
            'items'            => $dmqr->Items,
            'hash_code'        => $hash_code,
            'current_datetime' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
    }

    public function _revert_to_pending(Request $request)
    {
        $id = (int) $request->input('id');

        $dmqr = DirectMaterialQuantityRequest::find($id);

        if (!$dmqr) {
            return response()->json([
                'status'  => 0,
                'message' => 'Record not found',
                'data'    => []
            ]);
        }

        if ($dmqr->status != 'APRV') {
            return response()->json([
                'status'  => 0,
                'message' => 'Direct Material Request is not in approved status.',
                'data'    => []
            ]);
        }

        $canRevertAll = $this->hasAccess(['material_request:all:revert_to_pending', 'direct_material_quantity_request:all:revert_to_pending', 'material_request:all:approve', 'direct_material_quantity_request:all:approve']);
        $canRevertOwn = $this->hasAccess(['material_request:own:revert_to_pending', 'direct_material_quantity_request:own:revert_to_pending']);

        if (!$canRevertAll && (!$canRevertOwn || $dmqr->created_by != auth()->user()->id)) {
            return response()->json([
                'status'  => 0,
                'message' => 'Access Denied',
                'data'    => []
            ]);
        }

        // Check if there are active dependent direct purchase orders
        $countPo = \App\Models\DirectPurchaseOrder::where('direct_material_quantity_request_id', $id)
            ->whereNotIn('status', ['VOID', 'REJC'])
            ->whereNull('deleted_at')
            ->count();

        if ($countPo > 0) {
            return response()->json([
                'status'  => 0,
                'message' => 'Record dependency issue, this Direct Material Request has active Purchase Order(s) that are dependent.',
                'data'    => []
            ]);
        }

        // Check if there are approved canvass quotes
        $countCanvass = \App\Models\DirectMaterialCanvass::where('direct_material_quantity_request_id', $id)
            ->where('status', 'APRV')
            ->whereNull('deleted_at')
            ->count();

        if ($countCanvass > 0) {
            return response()->json([
                'status'  => 0,
                'message' => 'Record dependency issue, this Direct Material Request has approved canvass quote(s) that are dependent.',
                'data'    => []
            ]);
        }

        DB::beginTransaction();

        try {
            $dmqr->status      = 'PEND';
            $dmqr->approved_by = null;
            $dmqr->approved_at = null;
            $dmqr->updated_by  = auth()->user()->id;
            $dmqr->save();

            DirectMaterialQuantityRequestItem::where('direct_material_quantity_request_id', $id)
                ->update(['status' => 'PEND']);

            DB::commit();

            return response()->json([
                'status'  => 1,
                'message' => 'Direct Material Request reverted to pending.',
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

    public function _for_direct_po($id)
    {
        $id = (int) $id;
        $dmqr = DirectMaterialQuantityRequest::with(['Project', 'Section', 'Items.MaterialItem'])->find($id);

        if (!$dmqr || $dmqr->status != 'APRV') {
            return response()->json([
                'status'  => 0,
                'message' => 'Direct Material Request is not approved or not found.',
                'data'    => []
            ]);
        }

        $availableItems = [];

        foreach ($dmqr->Items as $item) {
            $approvedCanvasses = DirectMaterialCanvass::with(['Supplier', 'PaymentTerm'])
                ->where('direct_material_quantity_request_item_id', $item->id)
                ->where('status', '=', 'APRV')
                ->get();

            if ($approvedCanvasses->isEmpty()) {
                continue;
            }

            $ordered = DirectPurchaseOrderItem::where('direct_material_quantity_request_item_id', $item->id)
                ->whereHas('DirectPurchaseOrder', function ($q) {
                    $q->where('status', '!=', 'VOID');
                })
                ->sum('quantity');

            $balance = $item->quantity - $ordered;

            if ($balance > 0) {
                foreach ($approvedCanvasses as $approvedCanvass) {
                    $availableItems[] = [
                        'direct_material_quantity_request_item_id' => $item->id,
                        'direct_material_canvass_id'               => $approvedCanvass->id,
                        'material_item_id'                         => $item->material_item_id,
                        'material_item_name'                       => $item->MaterialItem ? $item->MaterialItem->formattedName : 'Unknown Item',
                        'supplier_id'                              => $approvedCanvass->supplier_id,
                        'supplier_name'                            => $approvedCanvass->Supplier ? $approvedCanvass->Supplier->name : '',
                        'payment_term_id'                          => $approvedCanvass->payment_term_id,
                        'payment_term_name'                        => $approvedCanvass->PaymentTerm ? $approvedCanvass->PaymentTerm->text : '',
                        'price'                                    => (float) $approvedCanvass->price,
                        'requested_quantity'                       => (float) $item->quantity,
                        'ordered_quantity'                         => (float) $ordered,
                        'balance_quantity'                         => (float) $balance,
                        'remarks'                                  => $item->remarks ?? ''
                    ];
                }
            }
        }

        return response()->json([
            'status'  => 1,
            'message' => '',
            'data'    => [
                'id'           => $dmqr->id,
                'formatted_id' => $dmqr->formatted_id,
                'project_id'   => $dmqr->project_id,
                'project_name' => $dmqr->Project ? $dmqr->Project->name : '',
                'section_id'   => $dmqr->section_id,
                'section_name' => $dmqr->Section ? $dmqr->Section->name : '',
                'items'        => $availableItems
            ]
        ]);
    }

    public function _available_for_direct_po()
    {
        $requests = DirectMaterialQuantityRequest::with(['Project', 'Section'])
            ->where('status', '=', 'APRV')
            ->orderBy('id', 'DESC')
            ->get();

        $result = [];
        foreach ($requests as $dmqr) {
            $hasBalance = false;
            foreach ($dmqr->Items as $item) {
                $hasApprovedCanvass = DirectMaterialCanvass::where('direct_material_quantity_request_item_id', $item->id)
                    ->where('status', '=', 'APRV')
                    ->exists();

                if (!$hasApprovedCanvass) continue;

                $ordered = DirectPurchaseOrderItem::where('direct_material_quantity_request_item_id', $item->id)
                    ->whereHas('DirectPurchaseOrder', function ($q) {
                        $q->where('status', '!=', 'VOID');
                    })
                    ->sum('quantity');

                if (($item->quantity - $ordered) > 0) {
                    $hasBalance = true;
                    break;
                }
            }

            if ($hasBalance) {
                $result[] = [
                    'id'           => $dmqr->id,
                    'formatted_id' => $dmqr->formatted_id,
                    'project_id'   => $dmqr->project_id,
                    'project_name' => $dmqr->Project ? $dmqr->Project->name : '',
                    'section_id'   => $dmqr->section_id,
                    'section_name' => $dmqr->Section ? $dmqr->Section->name : ''
                ];
            }
        }

        return response()->json([
            'status'  => 1,
            'message' => '',
            'data'    => $result
        ]);
    }
}
