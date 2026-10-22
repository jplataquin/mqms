<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Section;
use App\Models\MaterialItem;
use App\Models\Supplier;
use App\Models\PaymentTerm;
use App\Models\DirectPurchaseOrder;
use App\Models\DirectPurchaseOrderItem;
use App\Models\DirectMaterialQuantityRequest;
use App\Models\DirectMaterialQuantityRequestItem;
use App\Models\DirectMaterialCanvass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DirectPurchaseOrderController extends Controller
{
    public function list()
    {
        $projects = Project::orderBy('name', 'ASC')->where('status', '=', 'ACTV')->get();
        $material_items = MaterialItem::orderBy('name', 'ASC')->get();
        $suppliers = Supplier::orderBy('name', 'ASC')->whereNull('deleted_at')->get();

        return view('direct_purchase_order/list', [
            'projects'       => $projects,
            'material_items' => $material_items,
            'suppliers'      => $suppliers
        ]);
    }

    public function _list(Request $request)
    {
        $user = auth()->user();

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

        if ($query) {
            $dpo = $dpo->where('id', '=', $query);
        }

        // Check roles / permissions
        $canViewAll = $this->hasAccess(['purchase_order:all:list', 'direct_purchase_order:all:list']);
        $canViewOwn = $this->hasAccess(['purchase_order:own:list', 'direct_purchase_order:own:list']);

        if ($canViewOwn && !$canViewAll) {
            $dpo = $dpo->where('created_by', '=', $user->id);
        } else if (!$canViewOwn && !$canViewAll) {
            return response()->json([
                'status'  => 0,
                'message' => 'Access Denied',
                'data'    => []
            ]);
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

        if ($status) {
            $dpo = $dpo->where('status', '=', $status);
        }

        if ($limit > 0) {
            $offset = ($page - 1) * $limit;
            $result = $dpo->orderBy($orderBy, $order)->skip($offset)->take($limit)->with(['Project', 'Section', 'Supplier', 'PaymentTerm'])->get();
        } else {
            $result = $dpo->orderBy($orderBy, $order)->take($limit)->with(['Project', 'Section', 'Supplier', 'PaymentTerm'])->get();
        }

        return response()->json([
            'status'  => 1,
            'message' => '',
            'data'    => $result
        ]);
    }

    public function create()
    {
        if (!$this->hasAccess(['purchase_order:own:create', 'direct_purchase_order:own:create'])) {
            return view('access_denied');
        }

        $projects       = Project::orderBy('name', 'ASC')->where('status', '=', 'ACTV')->get();
        $suppliers      = Supplier::orderBy('name', 'ASC')->whereNull('deleted_at')->get();
        $payment_terms  = PaymentTerm::orderBy('text', 'ASC')->get();
        $material_items = MaterialItem::orderBy('name', 'ASC')->get();

        $available_dmqrs = DirectMaterialQuantityRequest::with(['Project', 'Section'])
            ->where('status', '=', 'APRV')
            ->orderBy('id', 'DESC')
            ->get()
            ->filter(function ($dmqr) {
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
                        return true;
                    }
                }
                return false;
            })->values();

        return view('direct_purchase_order/create', [
            'projects'        => $projects,
            'suppliers'       => $suppliers,
            'payment_terms'   => $payment_terms,
            'material_items'  => $material_items,
            'available_dmqrs' => $available_dmqrs
        ]);
    }

    public function _create(Request $request)
    {
        if (!$this->hasAccess(['purchase_order:own:create', 'direct_purchase_order:own:create'])) {
            return response()->json([
                'status'  => 0,
                'message' => 'Access Denied',
                'data'    => []
            ]);
        }

        $supplier_id     = (int) $request->input('supplier_id');
        $payment_term_id = (int) $request->input('payment_term_id');

        $raw_project_id  = $request->input('project_id');
        $project_id      = (!empty($raw_project_id) && $raw_project_id !== 'null' && $raw_project_id !== 'undefined' && is_numeric($raw_project_id))
            ? (int) $raw_project_id
            : null;

        $raw_section_id  = $request->input('section_id');
        $section_id      = (!empty($raw_section_id) && $raw_section_id !== 'null' && $raw_section_id !== 'undefined' && is_numeric($raw_section_id) && (int) $raw_section_id > 0)
            ? (int) $raw_section_id
            : null;

        $raw_dmqr_id     = $request->input('direct_material_quantity_request_id');
        $dmqr_id         = (!empty($raw_dmqr_id) && $raw_dmqr_id !== 'null' && $raw_dmqr_id !== 'undefined' && is_numeric($raw_dmqr_id) && (int) $raw_dmqr_id > 0)
            ? (int) $raw_dmqr_id
            : null;

        if ($dmqr_id) {
            $dmqr = DirectMaterialQuantityRequest::find($dmqr_id);
            if (!$dmqr || $dmqr->status != 'APRV') {
                return response()->json([
                    'status'  => -2,
                    'message' => 'The selected Direct Material Request is not approved or not found.',
                    'data'    => []
                ]);
            }
        }

        try {
            $po_items = json_decode($request->input('items'), true);
            $extras   = json_decode($request->input('extras'), true) ?? [];

            if (is_null($po_items) || !is_array($po_items) || count($po_items) === 0) {
                return response()->json([
                    'status'  => 0,
                    'message' => 'At least one item is required to make a direct PO.',
                    'data'    => []
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 0,
                'message' => $e->getMessage(),
                'data'    => []
            ]);
        }

        $itemsValidation = Validator::make(['items' => $po_items], [
            'items.*.material_item_id' => ['required', 'integer'],
            'items.*.order_quantity'   => ['required', 'numeric', 'min:0.01'],
            'items.*.price'            => ['required', 'numeric', 'min:0'],
            'items.*.remarks'          => ['nullable', 'string', 'max:255'],
        ]);

        if ($itemsValidation->fails()) {
            return response()->json([
                'status'  => -2,
                'message' => 'Failed Validation',
                'data'    => $itemsValidation->messages()
            ]);
        }

        if ($dmqr_id) {
            foreach ($po_items as $item) {
                $dmqr_item_id = $item['direct_material_quantity_request_item_id'] ?? null;
                if ($dmqr_item_id) {
                    $dmqrItem = DirectMaterialQuantityRequestItem::where('id', $dmqr_item_id)
                        ->where('direct_material_quantity_request_id', $dmqr_id)
                        ->first();

                    if (!$dmqrItem) {
                        return response()->json([
                            'status'  => -2,
                            'message' => 'Line item does not belong to the selected Direct Material Request.',
                            'data'    => []
                        ]);
                    }

                    $ordered = DirectPurchaseOrderItem::where('direct_material_quantity_request_item_id', $dmqr_item_id)
                        ->whereHas('DirectPurchaseOrder', function ($q) {
                            $q->where('status', '!=', 'VOID');
                        })
                        ->sum('quantity');

                    $remainingBalance = (float) $dmqrItem->quantity - (float) $ordered;
                    if ((float) $item['order_quantity'] > $remainingBalance + 0.0001) {
                        return response()->json([
                            'status'  => -2,
                            'message' => "Order quantity ({$item['order_quantity']}) exceeds remaining balance ({$remainingBalance}) for requested item #{$dmqr_item_id}.",
                            'data'    => []
                        ]);
                    }
                }
            }
        }

        $validation = Validator::make([
            'supplier_id'     => $supplier_id,
            'payment_term_id' => $payment_term_id,
            'project_id'      => $project_id,
            'section_id'      => $section_id,
        ], [
            'supplier_id'     => ['required', 'integer', 'min:1', 'exists:suppliers,id'],
            'payment_term_id' => ['required', 'integer', 'min:1', 'exists:payment_terms,id'],
            'project_id'      => ['required', 'integer', 'min:1', 'exists:projects,id'],
            'section_id'      => ['nullable', 'integer', 'exists:sections,id'],
        ], [
            'payment_term_id.required' => 'Payment Term is required.',
            'payment_term_id.min'      => 'Payment Term is required.',
            'payment_term_id.exists'   => 'The selected Payment Term is invalid.',
            'supplier_id.required'     => 'Supplier is required.',
            'supplier_id.min'          => 'Supplier is required.',
            'supplier_id.exists'       => 'The selected Supplier is invalid.',
            'project_id.required'      => 'Project is required.',
            'project_id.min'           => 'Project is required.',
            'project_id.exists'        => 'The selected Project is invalid.',
            'section_id.exists'        => 'The selected Section is invalid.',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status'  => -2,
                'message' => 'Failed Validation',
                'data'    => $validation->messages()
            ]);
        }

        if ($section_id && $project_id) {
            $sectionExists = Section::where('id', $section_id)->where('project_id', $project_id)->exists();
            if (!$sectionExists) {
                return response()->json([
                    'status'  => -2,
                    'message' => 'The selected Section does not belong to the selected Project.',
                    'data'    => []
                ]);
            }
        }

        $user_id = Auth::user()->id;

        DB::beginTransaction();

        try {
            $dpo = new DirectPurchaseOrder();
            $dpo->status                              = 'DRFT';
            $dpo->supplier_id                         = $supplier_id;
            $dpo->payment_term_id                     = $payment_term_id;
            $dpo->project_id                          = $project_id;
            $dpo->section_id                          = $section_id;
            $dpo->direct_material_quantity_request_id = $dmqr_id;
            $dpo->extras                              = json_encode($extras);
            $dpo->created_by                          = $user_id;
            $dpo->save();

            $bulk = [];
            foreach ($po_items as $item) {
                $bulk[] = [
                    'direct_purchase_order_id'                 => $dpo->id,
                    'direct_material_quantity_request_item_id' => $item['direct_material_quantity_request_item_id'] ?? null,
                    'direct_material_canvass_id'               => $item['direct_material_canvass_id'] ?? null,
                    'material_item_id'                         => $item['material_item_id'],
                    'status'                                   => 'PEND',
                    'quantity'                                 => $item['order_quantity'],
                    'price'                                    => $item['price'],
                    'remarks'                                  => $item['remarks'] ?? null,
                    'created_at'                               => Carbon::now(),
                    'updated_at'                               => Carbon::now(),
                ];
            }

            DirectPurchaseOrderItem::insert($bulk);

            DB::commit();

            return response()->json([
                'status'  => 1,
                'message' => '',
                'data'    => ['id' => $dpo->id]
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
        $dpo = DirectPurchaseOrder::with(['Items.MaterialItem', 'Supplier', 'PaymentTerm', 'Project', 'Section'])->findOrFail($id);
        $user = auth()->user();

        $canViewAll = $this->hasAccess(['purchase_order:all:view', 'direct_purchase_order:all:view']);
        $canViewOwn = $this->hasAccess(['purchase_order:own:view', 'direct_purchase_order:own:view']);

        if (!$canViewAll) {
            if (!$canViewOwn || $dpo->created_by != $user->id) {
                return view('access_denied');
            }
        }

        $extras = json_decode($dpo->extras) ?? [];

        $dpo_details = [
            "DPO Number"      => str_pad($dpo->id, 6, '0', STR_PAD_LEFT),
            "Supplier"        => $dpo->Supplier ? $dpo->Supplier->name : 'N/A',
            "Payment Term"    => $dpo->PaymentTerm ? $dpo->PaymentTerm->text : 'N/A',
            "Project"         => $dpo->Project ? $dpo->Project->name : 'None',
            "Section"         => $dpo->Section ? $dpo->Section->name : 'None',
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

        $canVoid        = $this->hasAccess(['purchase_order:all:void', 'direct_purchase_order:all:void', 'purchase_order:own:void', 'direct_purchase_order:own:void']);
        $canRequestVoid = $this->hasAccess(['purchase_order:all:request_void', 'direct_purchase_order:all:request_void', 'purchase_order:own:request_void', 'direct_purchase_order:own:request_void']);

        return view('direct_purchase_order/display', [
            'direct_purchase_order' => $dpo,
            'supplier'              => $dpo->Supplier,
            'payment_term'          => $dpo->PaymentTerm,
            'items'                 => $dpo->Items,
            'extras'                => $extras,
            'dpo_details'           => $dpo_details,
            'canVoid'               => $canVoid,
            'canRequestVoid'        => $canRequestVoid
        ]);
    }

    public function _submit_for_review(Request $request)
    {
        $id = (int) $request->input('id');

        DB::beginTransaction();

        try {
            $dpo = DirectPurchaseOrder::lockForUpdate()->find($id);

            if (!$dpo) {
                DB::rollback();
                return response()->json([
                    'status'  => 0,
                    'message' => 'Record not found',
                    'data'    => []
                ]);
            }

            $user = auth()->user();
            $canUpdateAll = $this->hasAccess(['purchase_order:all:update', 'direct_purchase_order:all:update']);
            $canUpdateOwn = $this->hasAccess(['purchase_order:own:update', 'direct_purchase_order:own:update']);

            if (!$canUpdateAll) {
                if (!$canUpdateOwn || $dpo->created_by != $user->id) {
                    DB::rollback();
                    return response()->json([
                        'status'  => 0,
                        'message' => 'Access Denied',
                        'data'    => []
                    ]);
                }
            }

            if ($dpo->status != 'DRFT') {
                DB::rollback();
                return response()->json([
                    'status'  => 0,
                    'message' => 'Record is not in status: DRFT',
                    'data'    => []
                ]);
            }

            if ($dpo->direct_material_quantity_request_id) {
                $dpoItems = DirectPurchaseOrderItem::where('direct_purchase_order_id', $dpo->id)->get();
                $groupedDpoItems = [];
                foreach ($dpoItems as $item) {
                    if ($item->direct_material_quantity_request_item_id) {
                        if (!isset($groupedDpoItems[$item->direct_material_quantity_request_item_id])) {
                            $groupedDpoItems[$item->direct_material_quantity_request_item_id] = 0;
                        }
                        $groupedDpoItems[$item->direct_material_quantity_request_item_id] += (float) $item->quantity;
                    }
                }

                if (!empty($groupedDpoItems)) {
                    $dmqrItemIds = array_keys($groupedDpoItems);
                    $dmqrItems = DirectMaterialQuantityRequestItem::whereIn('id', $dmqrItemIds)
                        ->lockForUpdate()
                        ->get()
                        ->keyBy('id');

                    foreach ($groupedDpoItems as $dmqr_item_id => $dpoItemQuantity) {
                        if (isset($dmqrItems[$dmqr_item_id])) {
                            $dmqrItem = $dmqrItems[$dmqr_item_id];

                            $ordered = DirectPurchaseOrderItem::where('direct_material_quantity_request_item_id', $dmqr_item_id)
                                ->whereHas('DirectPurchaseOrder', function ($q) {
                                    $q->whereNotIn('status', ['VOID', 'DRFT']);
                                })
                                ->sum('quantity');

                            if (((float) $ordered + $dpoItemQuantity) > (float) $dmqrItem->quantity + 0.0001) {
                                DB::rollback();
                                return response()->json([
                                    'status'  => -2,
                                    'message' => "Order quantity exceeds remaining balance for requested item #{$dmqr_item_id}. Please review quantities.",
                                    'data'    => []
                                ]);
                            }
                        }
                    }
                }
            }

            $dpo->status     = 'PEND';
            $dpo->updated_by = $user->id;
            $dpo->save();

            DB::commit();

            return response()->json([
                'status'  => 1,
                'message' => '',
                'data'    => []
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status'  => 0,
                'message' => 'An error occurred: ' . $e->getMessage(),
                'data'    => []
            ]);
        }
    }

    public function _delete(Request $request)
    {
        $id = (int) $request->input('id');
        $dpo = DirectPurchaseOrder::find($id);

        if (!$dpo) {
            return response()->json([
                'status'  => 0,
                'message' => 'Record not found',
                'data'    => []
            ]);
        }

        $user = auth()->user();
        $canDeleteAll = $this->hasAccess(['purchase_order:all:delete', 'direct_purchase_order:all:delete']);
        $canDeleteOwn = $this->hasAccess(['purchase_order:own:delete', 'direct_purchase_order:own:delete']);

        if (!$canDeleteAll) {
            if (!$canDeleteOwn || $dpo->created_by != $user->id) {
                return response()->json([
                    'status'  => 0,
                    'message' => 'Access Denied',
                    'data'    => []
                ]);
            }
        }

        if ($dpo->status != 'PEND' && $dpo->status != 'DRFT') {
            return response()->json([
                'status'  => 0,
                'message' => 'Record cannot be deleted, status is not pending or draft',
                'data'    => []
            ]);
        }

        DB::beginTransaction();

        try {
            $dpo->forceDelete();
            DirectPurchaseOrderItem::where('direct_purchase_order_id', $id)->delete();

            DB::commit();

            return response()->json([
                'status'  => 1,
                'message' => '',
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

    public function _request_void(Request $request)
    {
        $id = (int) $request->input('id');
        $dpo = DirectPurchaseOrder::find($id);

        if (!$dpo) {
            return response()->json([
                'status'  => 0,
                'message' => 'Record not found',
                'data'    => []
            ]);
        }

        $user = auth()->user();
        $canVoidAll = $this->hasAccess(['purchase_order:all:request_void', 'direct_purchase_order:all:request_void']);
        $canVoidOwn = $this->hasAccess(['purchase_order:own:request_void', 'direct_purchase_order:own:request_void']);

        if (!$canVoidAll) {
            if (!$canVoidOwn || $dpo->created_by != $user->id) {
                return response()->json([
                    'status'  => 0,
                    'message' => 'Access Denied',
                    'data'    => []
                ]);
            }
        }

        if ($dpo->status != 'APRV') {
            return response()->json([
                'status'  => 0,
                'message' => 'Cannot request void, record has wrong status',
                'data'    => []
            ]);
        }

        DB::beginTransaction();

        try {
            $dpo->approved_by = $user->id;
            $dpo->status      = 'REVO';
            $dpo->approved_at = Carbon::now();
            $dpo->save();

            DB::commit();

            return response()->json([
                'status'  => 1,
                'message' => '',
                'data'    => ['id' => $dpo->id]
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
        $dpo = DirectPurchaseOrder::find($id);

        if (!$dpo) {
            return response()->json([
                'status'  => 0,
                'message' => 'Record not found',
                'data'    => []
            ]);
        }

        $user = auth()->user();
        $canVoidAll = $this->hasAccess(['purchase_order:all:void', 'direct_purchase_order:all:void']);
        $canVoidOwn = $this->hasAccess(['purchase_order:own:void', 'direct_purchase_order:own:void']);

        if (!$canVoidAll) {
            if (!$canVoidOwn || $dpo->created_by != $user->id) {
                return response()->json([
                    'status'  => 0,
                    'message' => 'Access Denied',
                    'data'    => []
                ]);
            }
        }

        if ($dpo->status != 'APRV' && $dpo->status != 'REVO') {
            return response()->json([
                'status'  => 0,
                'message' => 'Record cannot be voided, status must be approved or void requested',
                'data'    => []
            ]);
        }

        DB::beginTransaction();

        try {
            $dpo->void_by = $user->id;
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

    public function print($id)
    {
        $dpo = DirectPurchaseOrder::with(['Items.MaterialItem', 'Supplier', 'PaymentTerm', 'Project', 'Section'])->findOrFail($id);
        $user = auth()->user();

        $canViewAll = $this->hasAccess(['purchase_order:all:view', 'direct_purchase_order:all:view']);
        $canViewOwn = $this->hasAccess(['purchase_order:own:view', 'direct_purchase_order:own:view']);

        if (!$canViewAll) {
            if (!$canViewOwn || $dpo->created_by != $user->id) {
                return view('access_denied');
            }
        }

        if ($dpo->status != 'APRV') {
            return abort(404);
        }

        $extras = json_decode($dpo->extras) ?? [];

        return view('direct_purchase_order/print', [
            'direct_purchase_order' => $dpo,
            'supplier'              => $dpo->Supplier,
            'payment_term'          => $dpo->PaymentTerm,
            'project'               => $dpo->Project,
            'section'               => $dpo->Section,
            'items'                 => $dpo->Items,
            'extras'                => $extras,
            'current_datetime'      => Carbon::now()
        ]);
    }
}
