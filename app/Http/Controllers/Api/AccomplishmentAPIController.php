<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accomplishment;

class AccomplishmentAPIController extends Controller
{
    /**
     * List accomplishments for 3rd party systems.
     * Authenticated via VerifyThirdPartyApiKey middleware.
     */
    public function list(Request $request)
    {
        $page             = (int) $request->input('page')     ?? 1;
        $limit            = (int) $request->input('limit')    ?? 10;
        $orderBy          = $request->input('order_by')       ?? 'id';
        $order            = $request->input('order')          ?? 'DESC';
        $componentId      = $request->input('component_id');
        $type             = $request->input('type');
        $entryData        = $request->input('entry_data');

        $query = Accomplishment::query();

        if ($componentId) {
            $query = $query->where('component_id', '=', $componentId);
        }

        if ($type != '') {
            $query = $query->where('type', '=', $type);
        }

        if ($entryData != '') {
            $query = $query->whereDate('entry_data', '=', $entryData);
        }

        if ($limit > 0) {
            $offset = ($page - 1) * $limit;
            $result = $query->orderBy($orderBy, $order)->skip($offset)->take($limit)->get();
        } else {
            $result = $query->orderBy($orderBy, $order)->get();
        }

        return response()->json([
            'status'  => 1,
            'message' => 'Success',
            'data'    => $result
        ]);
    }
}
