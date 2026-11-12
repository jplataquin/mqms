<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Section;
use App\Models\MaterialItem;
use App\Models\DirectMaterialQuantityRequest;
use App\Models\DirectMaterialQuantityRequestItem;
use App\Models\DirectMaterialCanvass;
use App\Models\DirectPurchaseOrder;
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

        $projects       = Project::where('status', '=', 'ACTV')->whereNull('deleted_at')->orderBy('name', 'ASC')->get();
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

        $raw_contract_item_id = $request->input('contract_item_id');
        $contract_item_id     = (!empty($raw_contract_item_id) && $raw_contract_item_id !== 'null' && $raw_contract_item_id !== 'undefined' && is_numeric($raw_contract_item_id) && (int) $raw_contract_item_id > 0)
            ? (int) $raw_contract_item_id
            : null;

        $raw_component_id     = $request->input('component_id');
        $component_id         = (!empty($raw_component_id) && $raw_component_id !== 'null' && $raw_component_id !== 'undefined' && is_numeric($raw_component_id) && (int) $raw_component_id > 0)
            ? (int) $raw_component_id
            : null;

        $description     = $request->input('description') ?? '';

        $raw_date_needed = $request->input('date_needed');
        $date_needed     = null;
        if (!empty($raw_date_needed) && $raw_date_needed !== 'null' && $raw_date_needed !== 'undefined') {
            $dt = \DateTime::createFromFormat('M d, Y', $raw_date_needed);
            if ($dt) {
                $date_needed = $dt->format('Y-m-d');
            } else {
                $ts = strtotime($raw_date_needed);
                if ($ts !== false) {
                    $date_needed = date('Y-m-d', $ts);
                }
            }
        }

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
            'project_id'         => $project_id,
            'contract_item_id'   => $contract_item_id,
            'component_id'       => $component_id,
            'items'              => $items,
        ], [
            'project_id'         => ['required', 'integer', 'min:1'],
            'contract_item_id'   => ['nullable', 'integer', 'min:1'],
            'component_id'       => ['nullable', 'integer', 'min:1'],
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

        $activeProject = Project::where('id', $project_id)->where('status', '=', 'ACTV')->first();
        if (!$activeProject) {
            return response()->json([
                'status'  => 0,
                'message' => 'The selected Project must be active.',
                'data'    => []
            ]);
        }

        DB::beginTransaction();

        try {
            $user = auth()->user();

            $dmqr = new DirectMaterialQuantityRequest();
            $dmqr->project_id       = $project_id;
            $dmqr->section_id       = $section_id;
            $dmqr->contract_item_id = $contract_item_id;
            $dmqr->component_id     = $component_id;
            $dmqr->description      = $description;
            $dmqr->date_needed      = $date_needed;
            $dmqr->status           = 'PEND';
            $dmqr->created_by       = $user->id;
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

        $canCloseAll = $this->hasAccess(['material_request:all:close', 'material_request:all:update', 'material_request:all:approve', 'direct_material_quantity_request:all:close', 'direct_material_quantity_request:all:update']);
        $canCloseOwn = $this->hasAccess(['material_request:own:close', 'material_request:own:update', 'direct_material_quantity_request:own:close', 'direct_material_quantity_request:own:update']);
        $canClose = $canCloseAll || ($canCloseOwn && $dmqr->created_by == $user->id);

        $canVoidAll = $this->hasAccess(['material_request:all:void', 'direct_material_quantity_request:all:void', 'direct_material_request:all:void']);
        $canVoidOwn = $this->hasAccess(['material_request:own:void', 'direct_material_quantity_request:own:void', 'direct_material_request:own:void']);
        $canVoid = $canVoidAll || ($canVoidOwn && $dmqr->created_by == $user->id);

        $canRequestVoidAll = $this->hasAccess(['material_request:all:request_void', 'direct_material_request:all:request_void', 'direct_material_quantity_request:all:request_void']);
        $canRequestVoidOwn = $this->hasAccess(['material_request:own:request_void', 'direct_material_request:own:request_void', 'direct_material_quantity_request:own:request_void']);
        $canRequestVoid = $canRequestVoidAll || ($canRequestVoidOwn && $dmqr->created_by == $user->id);

        $material_items = MaterialItem::orderBy('name', 'ASC')->get();

        return view('direct_material_quantity_request/display', [
            'request'        => $dmqr,
            'items'          => $dmqr->Items,
            'canEdit'        => $canEdit,
            'canDelete'      => $canDelete,
            'canClose'       => $canClose,
            'canVoid'        => $canVoid,
            'canRequestVoid' => $canRequestVoid,
            'material_items' => $material_items
        ]);
    }

    public function po_list($id)
    {
        $id = (int) $id;
        $dmqr = DirectMaterialQuantityRequest::with(['Project', 'Section'])->findOrFail($id);

        $user = auth()->user();
        $canViewAll = $this->hasAccess(['material_request:all:view', 'direct_material_quantity_request:all:view']);
        $canViewOwn = $this->hasAccess(['material_request:own:view', 'direct_material_quantity_request:own:view']);

        if (!$canViewAll) {
            if (!$canViewOwn || $dmqr->created_by != $user->id) {
                return view('access_denied');
            }
        }

        $project = $dmqr->Project;
        $section = $dmqr->Section;

        $po_list = DirectPurchaseOrder::withTrashed()
            ->where('direct_material_quantity_request_id', $dmqr->id)
            ->with(['Supplier'])
            ->orderBy('created_at', 'desc')
            ->get();

        $draft    = [];
        $pending  = [];
        $approved = [];
        $rejected = [];
        $void     = [];
        $deleted  = [];

        foreach ($po_list as $po) {
            if ($po->deleted_at !== null) {
                $deleted[] = $po;
            } elseif ($po->status === 'DRFT') {
                $draft[] = $po;
            } elseif ($po->status === 'PEND') {
                $pending[] = $po;
            } elseif ($po->status === 'APRV') {
                $approved[] = $po;
            } elseif ($po->status === 'REJC') {
                $rejected[] = $po;
            } elseif ($po->status === 'VOID' || $po->status === 'REVO') {
                $void[] = $po;
            }
        }

        return view('direct_material_quantity_request/po_list', [
            'project'     => $project,
            'section'     => $section,
            'request'     => $dmqr,
            'draft'       => $draft,
            'pending'     => $pending,
            'approved'    => $approved,
            'rejected'    => $rejected,
            'void'        => $void,
            'deleted'     => $deleted,
            'total_count' => count($po_list),
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
                    $q->whereIn('status', ['PEND', 'APRV']);
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
                'project_id'       => $dmqr->project_id,
                'project_name'     => $dmqr->Project ? $dmqr->Project->name : '',
                'section_id'       => $dmqr->section_id,
                'section_name'     => $dmqr->Section ? $dmqr->Section->name : '',
                'contract_item_id' => $dmqr->contract_item_id,
                'component_id'     => $dmqr->component_id,
                'date_needed'      => $dmqr->date_needed,
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
                        $q->whereIn('status', ['PEND', 'APRV']);
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
                    'section_name' => $dmqr->Section ? $dmqr->Section->name : '',
                    'date_needed'  => $dmqr->date_needed
                ];
            }
        }

        return response()->json([
            'status'  => 1,
            'message' => '',
            'data'    => $result
        ]);
    }

    public function _get_total_po_quantity($item_id)
    {
        $item = DirectMaterialQuantityRequestItem::find($item_id);

        if (!$item) {
            return [
                'status'  => 0,
                'message' => 'Record not found (Direct Material Quantity Request Item)',
                'data'    => []
            ];
        }

        $total = DirectPurchaseOrderItem::where('direct_material_quantity_request_item_id', $item_id)
            ->whereHas('DirectPurchaseOrder', function ($q) {
                $q->whereIn('status', ['PEND', 'APRV']);
            })
            ->sum('quantity');

        return [
            'status'  => 1,
            'message' => '',
            'data'    => [
                'total' => (float) $total
            ]
        ];
    }

    public function _update(Request $request)
    {
        $id          = (int) $request->input('id');
        $description = $request->input('description') ?? '';
        $raw_date    = $request->input('date_needed');
        $items_raw   = $request->input('items');
        $delete_raw  = $request->input('delete_items');

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

        if (!$canUpdateAll) {
            if (!$canUpdateOwn || $dmqr->created_by != $user->id) {
                return response()->json([
                    'status'  => 0,
                    'message' => 'Access Denied',
                    'data'    => []
                ]);
            }
        }

        if (!in_array($dmqr->status, ['PEND', 'DRFT'])) {
            return response()->json([
                'status'  => 0,
                'message' => 'This record cannot be updated (Status: ' . $dmqr->status . ')',
                'data'    => []
            ]);
        }

        $date_needed = null;
        if (!empty($raw_date) && $raw_date !== 'null' && $raw_date !== 'undefined') {
            $dt = \DateTime::createFromFormat('M d, Y', $raw_date);
            if ($dt) {
                $date_needed = $dt->format('Y-m-d');
            } else {
                $ts = strtotime($raw_date);
                if ($ts !== false) {
                    $date_needed = date('Y-m-d', $ts);
                }
            }
        }

        try {
            $items = is_array($items_raw) ? $items_raw : json_decode($items_raw, true);
            if (is_null($items) || !is_array($items) || count($items) === 0) {
                return response()->json([
                    'status'  => 0,
                    'message' => 'At least one item is required.',
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

        $delete_items = [];
        if (!empty($delete_raw)) {
            $delete_items = is_array($delete_raw) ? $delete_raw : json_decode($delete_raw, true);
            if (!is_array($delete_items)) {
                $delete_items = [];
            }
        }

        $validator = Validator::make([
            'items' => $items
        ], [
            'items'                    => ['required', 'array', 'min:1'],
            'items.*.material_item_id' => ['required', 'integer'],
            'items.*.quantity'         => ['required', 'numeric', 'min:0.01'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 0,
                'message' => $validator->errors()->first(),
                'data'    => []
            ]);
        }

        // Validate PO quantities for modified items
        foreach ($items as $item) {
            $itemId = isset($item['id']) ? (int) $item['id'] : 0;
            if (!$itemId) continue;

            $poResult = $this->_get_total_po_quantity($itemId);
            if ($poResult['status'] > 0) {
                $poQty = $poResult['data']['total'];
                if ((float) $item['quantity'] < $poQty) {
                    return response()->json([
                        'status'  => 0,
                        'message' => "Requested quantity cannot be lower than the total PO'd quantity (" . $poQty . ")",
                        'data'    => [
                            'item_id'     => $itemId,
                            'quantity'    => $item['quantity'],
                            'po_quantity' => $poQty
                        ]
                    ]);
                }
            }
        }

        // Validate PO quantities for deleted items
        foreach ($delete_items as $delItemId) {
            $delItemId = (int) $delItemId;
            $poResult = $this->_get_total_po_quantity($delItemId);
            if ($poResult['status'] > 0 && $poResult['data']['total'] > 0) {
                return response()->json([
                    'status'  => 0,
                    'message' => "An item cannot be deleted because it has a maintained PO quantity",
                    'data'    => []
                ]);
            }
        }

        DB::beginTransaction();

        try {
            $dmqr->description = $description;
            if ($date_needed) {
                $dmqr->date_needed = $date_needed;
            }
            $dmqr->updated_by = $user->id;
            $dmqr->save();

            foreach ($items as $item) {
                $itemId = isset($item['id']) ? (int) $item['id'] : 0;
                $remarks = $item['remarks'] ?? null;

                if (!$itemId) {
                    // New item
                    $newItem = new DirectMaterialQuantityRequestItem();
                    $newItem->direct_material_quantity_request_id = $dmqr->id;
                    $newItem->material_item_id                     = (int) $item['material_item_id'];
                    $newItem->quantity                             = (float) $item['quantity'];
                    $newItem->remarks                              = $remarks;
                    $newItem->status                               = 'PEND';
                    $newItem->save();
                } else {
                    // Existing item
                    $existingItem = DirectMaterialQuantityRequestItem::find($itemId);
                    if ($existingItem && $existingItem->direct_material_quantity_request_id == $dmqr->id) {
                        $existingItem->material_item_id = (int) $item['material_item_id'];
                        $existingItem->quantity         = (float) $item['quantity'];
                        $existingItem->remarks          = $remarks;
                        $existingItem->save();
                    }
                }
            }

            if (count($delete_items) > 0) {
                DirectMaterialQuantityRequestItem::where('direct_material_quantity_request_id', $dmqr->id)
                    ->whereIn('id', $delete_items)
                    ->delete();
            }

            DB::commit();

            return response()->json([
                'status'  => 1,
                'message' => 'Direct Material Request updated successfully',
                'data'    => ['id' => $dmqr->id]
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

    public function _close(Request $request)
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
                'message' => 'Direct Material Request is not in Approved status',
                'data'    => []
            ]);
        }

        $user = auth()->user();
        $canCloseAll = $this->hasAccess(['material_request:all:close', 'material_request:all:update', 'material_request:all:approve', 'direct_material_quantity_request:all:close', 'direct_material_quantity_request:all:update']);
        $canCloseOwn = $this->hasAccess(['material_request:own:close', 'material_request:own:update', 'direct_material_quantity_request:own:close', 'direct_material_quantity_request:own:update']);

        if (!$canCloseAll) {
            if (!$canCloseOwn || $dmqr->created_by != $user->id) {
                return response()->json([
                    'status'  => 0,
                    'message' => 'Access Denied',
                    'data'    => []
                ]);
            }
        }

        DB::beginTransaction();

        try {
            $items = $dmqr->Items;

            foreach ($items as $item) {
                $totalPoQty = DirectPurchaseOrderItem::where('direct_material_quantity_request_item_id', $item->id)
                    ->whereHas('DirectPurchaseOrder', function ($q) {
                        $q->whereIn('status', ['APRV', 'PEND']);
                    })
                    ->sum('quantity');

                $item->original_quantity = $item->quantity;
                $item->quantity          = (float) $totalPoQty;
                $item->status            = 'CLSD';
                $item->save();
            }

            $dmqr->status     = 'CLSD';
            $dmqr->updated_by = $user->id;
            $dmqr->save();

            DB::commit();

            return response()->json([
                'status'  => 1,
                'message' => 'Direct Material Request closed successfully',
                'data'    => ['id' => $dmqr->id]
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

    public function _request_void(Request $request)
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
        $canRequestVoidAll = $this->hasAccess(['material_request:all:request_void', 'direct_material_request:all:request_void', 'direct_material_quantity_request:all:request_void']);
        $canRequestVoidOwn = $this->hasAccess(['material_request:own:request_void', 'direct_material_request:own:request_void', 'direct_material_quantity_request:own:request_void']);

        if (!$canRequestVoidAll) {
            if (!$canRequestVoidOwn || $dmqr->created_by != $user->id) {
                return response()->json([
                    'status'  => 0,
                    'message' => 'Access Denied',
                    'data'    => []
                ]);
            }
        }

        if (!in_array($dmqr->status, ['APRV', 'CLSD'])) {
            return response()->json([
                'status'  => 0,
                'message' => 'Record cannot be requested for void, status must be approved or closed',
                'data'    => []
            ]);
        }

        // Check if any non-void direct purchase orders exist
        $activeDpoCount = DirectPurchaseOrder::where('direct_material_quantity_request_id', $dmqr->id)
            ->where('status', '!=', 'VOID')
            ->count();

        if ($activeDpoCount > 0) {
            return response()->json([
                'status'  => 0,
                'message' => 'Cannot request void: Existing purchase orders must first be voided before the material quantity request can be set to status request void.',
                'data'    => []
            ]);
        }

        DB::beginTransaction();

        try {
            $dmqr->previous_status = $dmqr->status;
            $dmqr->status          = 'REVO';
            $dmqr->save();

            DB::commit();

            return response()->json([
                'status'  => 1,
                'message' => 'Direct Material Request void requested successfully',
                'data'    => ['id' => $dmqr->id]
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
        $canVoidAll = $this->hasAccess(['material_request:all:void', 'direct_material_quantity_request:all:void', 'direct_material_request:all:void']);
        $canVoidOwn = $this->hasAccess(['material_request:own:void', 'direct_material_quantity_request:own:void', 'direct_material_request:own:void']);

        if (!$canVoidAll) {
            if (!$canVoidOwn || $dmqr->created_by != $user->id) {
                return response()->json([
                    'status'  => 0,
                    'message' => 'Access Denied',
                    'data'    => []
                ]);
            }
        }

        if (!in_array($dmqr->status, ['APRV', 'CLSD', 'REVO'])) {
            return response()->json([
                'status'  => 0,
                'message' => 'Record cannot be voided, status must be approved, closed, or request void',
                'data'    => []
            ]);
        }

        // Check if any non-void direct purchase orders exist
        $activeDpoCount = DirectPurchaseOrder::where('direct_material_quantity_request_id', $dmqr->id)
            ->where('status', '!=', 'VOID')
            ->count();

        if ($activeDpoCount > 0) {
            return response()->json([
                'status'  => 0,
                'message' => 'Cannot void request: Existing purchase orders must first be voided before the material quantity request can be set to status void.',
                'data'    => []
            ]);
        }

        DB::beginTransaction();

        try {
            $dmqr->void_by = $user->id;
            $dmqr->void_at = Carbon::now();
            $dmqr->status  = 'VOID';
            $dmqr->save();

            DB::table('direct_material_quantity_request_items')
                ->where('direct_material_quantity_request_id', $dmqr->id)
                ->update(['status' => 'VOID']);

            DB::commit();

            return response()->json([
                'status'  => 1,
                'message' => 'Direct Material Request voided successfully',
                'data'    => ['id' => $dmqr->id]
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
