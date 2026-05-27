<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Section;

class SectionAPIController extends Controller
{
    /**
     * List sections for 3rd party systems.
     * Authenticated via VerifyThirdPartyApiKey middleware.
     */
    public function list(Request $request)
    {
        $page       = (int) $request->input('page')     ?? 1;
        $limit      = (int) $request->input('limit')    ?? 10;
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $query      = $request->input('query')          ?? '';
        $projectId  = $request->input('project_id');

        $sectionQuery = Section::query();

        if ($query != '') {
            $sectionQuery = $sectionQuery->where('name', 'LIKE', '%' . $query . '%');
        }

        if ($projectId) {
            $sectionQuery = $sectionQuery->where('project_id', '=', $projectId);
        }

        if ($limit > 0) {
            $offset = ($page - 1) * $limit;
            $result = $sectionQuery->orderBy($orderBy, $order)->skip($offset)->take($limit)->get();
        } else {
            $result = $sectionQuery->orderBy($orderBy, $order)->get();
        }

        return response()->json([
            'status'  => 1,
            'message' => 'Success',
            'data'    => $result
        ]);
    }
}
