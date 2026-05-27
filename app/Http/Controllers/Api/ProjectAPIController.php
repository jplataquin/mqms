<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;

class ProjectAPIController extends Controller
{
    /**
     * List projects for 3rd party systems.
     * Authenticated via VerifyThirdPartyApiKey middleware.
     */
    public function list(Request $request)
    {
        $page       = (int) $request->input('page')     ?? 1;
        $limit      = (int) $request->input('limit')    ?? 10;
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $query      = $request->input('query')          ?? '';
        $status     = $request->input('status')         ?? '';

        $projectQuery = Project::query();

        if ($query != '') {
            $projectQuery = $projectQuery->where('name', 'LIKE', '%' . $query . '%');
        }

        if ($status != '') {
            $projectQuery = $projectQuery->where('status', '=', $status);
        }

        if ($limit > 0) {
            $offset = ($page - 1) * $limit;
            $result = $projectQuery->orderBy($orderBy, $order)->skip($offset)->take($limit)->get();
        } else {
            $result = $projectQuery->orderBy($orderBy, $order)->get();
        }

        return response()->json([
            'status'  => 1,
            'message' => 'Success',
            'data'    => $result
        ]);
    }
}
