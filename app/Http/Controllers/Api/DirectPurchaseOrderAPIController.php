<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DirectPurchaseOrder;

class DirectPurchaseOrderAPIController extends Controller
{
    /**
     * List direct purchase orders for 3rd party systems.
     * Authenticated via VerifyThirdPartyApiKey middleware.
     */
    public function list(Request $request)
    {
        $page       = (int) ($request->input('page') ?? 1);
        $limit      = $request->has('limit') ? (int) $request->input('limit') : 10;
        $orderBy    = $request->input('order_by') ?? 'id';
        $order      = strtoupper($request->input('order', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
        $query      = $request->input('query') ?? '';

        $dpoQuery = DirectPurchaseOrder::query()->with([
            'Items.MaterialItem',
            'Supplier',
            'PaymentTerm',
            'Project',
            'Section',
            'DirectMaterialQuantityRequest',
        ]);

        // General search query
        if ($query !== '') {
            $dpoQuery->where(function ($q) use ($query) {
                if (is_numeric($query)) {
                    $q->orWhere('id', (int) $query);
                }
                $q->orWhereHas('Supplier', function ($sq) use ($query) {
                    $sq->where('name', 'LIKE', '%' . $query . '%');
                })->orWhereHas('Project', function ($pq) use ($query) {
                    $pq->where('name', 'LIKE', '%' . $query . '%');
                });
            });
        }

        // Exact ID filter
        if ($request->filled('id')) {
            $dpoQuery->where('id', $request->input('id'));
        }

        // Project filter
        if ($projectId = $request->input('project_id') ?? $request->input('project')) {
            $dpoQuery->where('project_id', $projectId);
        }

        // Section filter
        if ($sectionId = $request->input('section_id') ?? $request->input('section')) {
            $dpoQuery->where('section_id', $sectionId);
        }

        // Contract Item / Component filter (Direct POs do not have components or contract items)
        if ($request->filled('contract_item_id') || $request->filled('contract_item') || $request->filled('component_id') || $request->filled('component')) {
            $dpoQuery->whereRaw('0 = 1');
        }

        // Supplier filter
        if ($supplierId = $request->input('supplier_id') ?? $request->input('supplier')) {
            $dpoQuery->where('supplier_id', $supplierId);
        }

        // Payment Term filter
        if ($paymentTermId = $request->input('payment_term_id') ?? $request->input('payment_terms') ?? $request->input('payment_term')) {
            $dpoQuery->where('payment_term_id', $paymentTermId);
        }

        // Status filter
        if ($status = $request->input('status')) {
            $dpoQuery->where('status', $status);
        }

        // Received Status filter
        if ($receivedStatus = $request->input('received_status') ?? $request->input('receive_status')) {
            $dpoQuery->where('received_status', $receivedStatus);
        }

        // Created by filter
        if ($createdBy = $request->input('created_by')) {
            $dpoQuery->where('created_by', $createdBy);
        }

        // Direct Material Quantity Request ID filter
        if ($dmqrId = $request->input('direct_material_quantity_request_id') ?? $request->input('material_quantity_request_id') ?? $request->input('dmqr_id')) {
            $dpoQuery->where('direct_material_quantity_request_id', $dmqrId);
        }

        // Contains specific material item filter
        if ($materialItemId = $request->input('material_item_id') ?? $request->input('material_item')) {
            $dpoQuery->whereHas('Items', function ($q) use ($materialItemId) {
                $q->where('material_item_id', $materialItemId);
            });
        }

        // Date Created filter
        if ($dateCreated = $request->input('date_created') ?? $request->input('created_at')) {
            $dpoQuery->whereDate('created_at', $dateCreated);
        }

        // Date range filters for created_at
        if ($dateFrom = $request->input('date_from')) {
            $dpoQuery->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $dpoQuery->whereDate('created_at', '<=', $dateTo);
        }

        // Date Needed filter
        if ($dateNeeded = $request->input('date_needed')) {
            $dpoQuery->whereDate('date_needed', $dateNeeded);
        }

        // Pagination
        if ($limit > 0) {
            $offset = ($page - 1) * $limit;
            $result = $dpoQuery->orderBy($orderBy, $order)->skip($offset)->take($limit)->get();
        } else {
            $result = $dpoQuery->orderBy($orderBy, $order)->get();
        }

        return response()->json([
            'status'  => 1,
            'message' => 'Success',
            'data'    => $result
        ]);
    }
}
