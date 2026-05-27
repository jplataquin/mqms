<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;

class SupplierAPIController extends Controller
{
    /**
     * List suppliers for 3rd party systems.
     * Authenticated via VerifyThirdPartyApiKey middleware.
     */
    public function list(Request $request)
    {
        $page       = (int) $request->input('page')     ?? 1;
        $limit      = (int) $request->input('limit')    ?? 10;
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $query      = $request->input('query')          ?? '';

        $supplierQuery = Supplier::query();

        if ($query != '') {
            $supplierQuery = $supplierQuery->where('name', 'LIKE', '%' . $query . '%');
        }

        if ($limit > 0) {
            $offset = ($page - 1) * $limit;
            $result = $supplierQuery->orderBy($orderBy, $order)->skip($offset)->take($limit)->get();
        } else {
            $result = $supplierQuery->orderBy($orderBy, $order)->get();
        }

        return response()->json([
            'status'  => 1,
            'message' => 'Success',
            'data'    => $result
        ]);
    }
}
