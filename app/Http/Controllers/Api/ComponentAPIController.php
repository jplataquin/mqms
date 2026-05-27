<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Component;

class ComponentAPIController extends Controller
{
    /**
     * List components for 3rd party systems.
     * Authenticated via VerifyThirdPartyApiKey middleware.
     */
    public function list(Request $request)
    {
        $page             = (int) $request->input('page')     ?? 1;
        $limit            = (int) $request->input('limit')    ?? 10;
        $orderBy          = $request->input('order_by')       ?? 'id';
        $order            = $request->input('order')          ?? 'DESC';
        $query            = $request->input('query')          ?? '';
        $status           = $request->input('status')         ?? '';
        $contractItemId   = $request->input('contract_item_id');
        $sectionId        = $request->input('section_id');
        $projectId        = $request->input('project_id');

        $componentQuery = Component::query();

        if ($query != '') {
            $componentQuery = $componentQuery->where('name', 'LIKE', '%' . $query . '%');
        }

        if ($status != '') {
            $componentQuery = $componentQuery->where('status', '=', $status);
        }

        if ($contractItemId) {
            $componentQuery = $componentQuery->where('contract_item_id', '=', $contractItemId);
        }

        if ($sectionId) {
            $componentQuery = $componentQuery->where('section_id', '=', $sectionId);
        }

        if ($projectId){
             $componentQuery = $componentQuery->where('project_id', '=', $sectionId);
        }

        if ($limit > 0) {
            $offset = ($page - 1) * $limit;
            $result = $componentQuery->orderBy($orderBy, $order)->skip($offset)->take($limit)->get();
        } else {
            $result = $componentQuery->orderBy($orderBy, $order)->get();
        }

        return response()->json([
            'status'  => 1,
            'message' => 'Success',
            'data'    => $result
        ]);
    }
}
