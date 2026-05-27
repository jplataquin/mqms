<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ApiCredential;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ApiCredentialController extends Controller
{
    public function list()
    {
        if (!$this->hasAccess('api_credential:view')) {
            return view('access_denied');
        }

        return view('api/list');
    }

    public function create()
    {
        if (!$this->hasAccess('api_credential:create')) {
            return view('access_denied');
        }

        return view('api/create');
    }

    public function _list(Request $request)
    {
        if (!$this->hasAccess('api_credential:view')) {
            return response()->json([
                'status'  => 0,
                'message' => 'Access Denied',
                'data'    => []
            ]);
        }

        $page       = (int) $request->input('page')     ?? 1;
        $limit      = (int) $request->input('limit')    ?? 10;
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $query      = $request->input('query')          ?? '';
        
        $credentials = ApiCredential::query();

        if ($query != '') {
            $credentials = $credentials->where('name', 'LIKE', '%' . $query . '%');
        }

        if ($limit > 0) {
            $offset = ($page - 1) * $limit;
            $result = $credentials->orderBy($orderBy, $order)->skip($offset)->take($limit)->get();
        } else {
            $result = $credentials->orderBy($orderBy, $order)->get();
        }

        return response()->json([
            'status'  => 1,
            'message' => '',
            'data'    => $result
        ]);
    }

    public function _create(Request $request)
    {
        if (!$this->hasAccess('api_credential:create')) {
            return response()->json([
                'status'  => 0,
                'message' => 'Access Denied',
                'data'    => []
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $credential = new ApiCredential();
        $credential->name       = $request->input('name');
        $credential->api_key    = Str::random(64);
        $credential->secret_key = Str::random(64);
        $credential->created_by = Auth::user()->id;
        $credential->save();

        return response()->json([
            'status'  => 1,
            'message' => 'API Credential created successfully',
            'data'    => $credential
        ]);
    }

    public function _delete(Request $request)
    {
        if (!$this->hasAccess('api_credential:delete')) {
            return response()->json([
                'status'  => 0,
                'message' => 'Access Denied',
                'data'    => []
            ]);
        }

        $id = (int) $request->input('id');
        $credential = ApiCredential::find($id);

        if (!$credential) {
            return response()->json([
                'status'  => 0,
                'message' => 'Credential not found',
                'data'    => []
            ]);
        }

        $credential->deleted_by = Auth::user()->id;
        $credential->save();
        $credential->delete();

        return response()->json([
            'status'  => 1,
            'message' => 'API Credential deleted successfully',
            'data'    => []
        ]);
    }
}
