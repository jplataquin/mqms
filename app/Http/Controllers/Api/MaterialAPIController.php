<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaterialItem;

class MaterialAPIController extends Controller
{
    /**
     * List material items for 3rd party systems.
     * Authenticated via VerifyThirdPartyApiKey middleware.
     */
    public function list(Request $request)
    {
        $page               = (int) $request->input('page')     ?? 1;
        $limit              = (int) $request->input('limit')    ?? 10;
        $orderBy            = $request->input('order_by')       ?? 'id';
        $order              = $request->input('order')          ?? 'DESC';
        $query              = $request->input('query')          ?? '';
        $materialGroupId    = $request->input('material_group_id');

        $materialQuery = MaterialItem::query();

        if ($query != '') {
            $materialQuery = $materialQuery->where('name', 'LIKE', '%' . $query . '%');
        }

        if ($materialGroupId) {
            $materialQuery = $materialQuery->where('material_group_id', '=', $materialGroupId);
        }

        if ($limit > 0) {
            $offset = ($page - 1) * $limit;
            $result = $materialQuery->orderBy($orderBy, $order)->skip($offset)->take($limit)->get();
        } else {
            $result = $materialQuery->orderBy($orderBy, $order)->get();
        }

        return response()->json([
            'status'  => 1,
            'message' => 'Success',
            'data'    => $result
        ]);
    }
}
