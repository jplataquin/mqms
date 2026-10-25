<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Schema;

class PurchaseOrderAPIController extends Controller
{
    /**
     * List purchase orders for 3rd party systems.
     * Authenticated via VerifyThirdPartyApiKey middleware.
     */
    public function list(Request $request)
    {
        $page       = (int) ($request->input('page') ?? 1);
        $limit      = $request->has('limit') ? (int) $request->input('limit') : 10;
        $orderBy    = $request->input('order_by') ?? 'id';
        $order      = strtoupper($request->input('order', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
        $query      = $request->input('query') ?? '';

        $poQuery = PurchaseOrder::query()->with([
            'Items.MaterialItem',
            'Supplier',
            'PaymentTerm',
            'Project',
            'Section',
            'Component',
            'MaterialQuantityRequest',
        ]);

        // General search query
        if ($query !== '') {
            $poQuery->where(function ($q) use ($query) {
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
            $poQuery->where('id', $request->input('id'));
        }

        // Project filter
        if ($projectId = $request->input('project_id') ?? $request->input('project')) {
            $poQuery->where('project_id', $projectId);
        }

        // Section filter
        if ($sectionId = $request->input('section_id') ?? $request->input('section')) {
            $poQuery->where('section_id', $sectionId);
        }

        // Contract Item filter
        if ($contractItemId = $request->input('contract_item_id') ?? $request->input('contract_item')) {
            $poQuery->where(function ($q) use ($contractItemId) {
                if (Schema::hasColumn('purchase_orders', 'contract_item_id')) {
                    $q->where('contract_item_id', $contractItemId);
                }
                $q->orWhereHas('Component', function ($compQ) use ($contractItemId) {
                    $compQ->where('contract_item_id', $contractItemId);
                });
            });
        }

        // Component filter
        if ($componentId = $request->input('component_id') ?? $request->input('component')) {
            $poQuery->where('component_id', $componentId);
        }

        // Supplier filter
        if ($supplierId = $request->input('supplier_id') ?? $request->input('supplier')) {
            $poQuery->where('supplier_id', $supplierId);
        }

        // Payment Term filter
        if ($paymentTermId = $request->input('payment_term_id') ?? $request->input('payment_terms') ?? $request->input('payment_term')) {
            $poQuery->where('payment_term_id', $paymentTermId);
        }

        // Status filter
        if ($status = $request->input('status')) {
            $poQuery->where('status', $status);
        }

        // Received Status filter
        if ($receivedStatus = $request->input('received_status') ?? $request->input('receive_status')) {
            $poQuery->where('received_status', $receivedStatus);
        }

        // Created by filter
        if ($createdBy = $request->input('created_by')) {
            $poQuery->where('created_by', $createdBy);
        }

        // Material Quantity Request ID filter
        if ($mqrId = $request->input('material_quantity_request_id') ?? $request->input('mqr_id')) {
            $poQuery->where('material_quantity_request_id', $mqrId);
        }

        // Contains specific material item filter
        if ($materialItemId = $request->input('material_item_id') ?? $request->input('material_item')) {
            $poQuery->whereHas('Items', function ($q) use ($materialItemId) {
                $q->where('material_item_id', $materialItemId);
            });
        }

        // Date Created filter
        if ($dateCreated = $request->input('date_created') ?? $request->input('created_at')) {
            $poQuery->whereDate('created_at', $dateCreated);
        }

        // Date range filters for created_at
        if ($dateFrom = $request->input('date_from')) {
            $poQuery->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $poQuery->whereDate('created_at', '<=', $dateTo);
        }

        // Date Needed filter
        if ($dateNeeded = $request->input('date_needed')) {
            $poQuery->where(function ($q) use ($dateNeeded) {
                if (Schema::hasColumn('purchase_orders', 'date_needed')) {
                    $q->whereDate('date_needed', $dateNeeded);
                } else {
                    $q->whereHas('MaterialQuantityRequest', function ($mqrQ) use ($dateNeeded) {
                        $mqrQ->whereDate('date_needed', $dateNeeded);
                    });
                }
            });
        }

        // Pagination
        if ($limit > 0) {
            $offset = ($page - 1) * $limit;
            $result = $poQuery->orderBy($orderBy, $order)->skip($offset)->take($limit)->get();
        } else {
            $result = $poQuery->orderBy($orderBy, $order)->get();
        }

        return response()->json([
            'status'  => 1,
            'message' => 'Success',
            'data'    => $result
        ]);
    }
}
