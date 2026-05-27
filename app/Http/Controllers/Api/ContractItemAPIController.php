<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContractItem;

class ContractItemAPIController extends Controller
{
    /**
     * List contract items for 3rd party systems.
     * Authenticated via VerifyThirdPartyApiKey middleware.
     */
    public function list(Request $request)
    {
        $page       = (int) $request->input('page')     ?? 1;
        $limit      = (int) $request->input('limit')    ?? 10;
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $query      = $request->input('query')          ?? '';
        $sectionId  = $request->input('section_id');

        $contractItemQuery = ContractItem::query();

        if ($query != '') {
            $contractItemQuery = $contractItemQuery->where('name', 'LIKE', '%' . $query . '%');
        }

        if ($sectionId) {
            $contractItemQuery = $contractItemQuery->where('section_id', '=', $sectionId);
        }

        if ($limit > 0) {
            $offset = ($page - 1) * $limit;
            $result = $contractItemQuery->orderBy($orderBy, $order)->skip($offset)->take($limit)->get();
        } else {
            $result = $contractItemQuery->orderBy($orderBy, $order)->get();
        }

        return response()->json([
            'status'  => 1,
            'message' => 'Success',
            'data'    => $result
        ]);
    }
}
