<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PersonalAccessTokenController extends Controller
{
    /**
     * Show the tokens list view.
     */
    public function list()
    {
        return view('personal_access_token/list');
    }

    /**
     * Show the token creation view.
     */
    public function create()
    {
        return view('personal_access_token/create');
    }

    /**
     * Get JSON list of tokens for the authenticated user.
     */
    public function _list(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status'  => 0,
                'message' => 'Unauthorized',
                'data'    => []
            ], 401);
        }

        $tokens = $user->tokens()->orderBy('id', 'desc')->get();

        return response()->json([
            'status'  => 1,
            'message' => '',
            'data'    => $tokens
        ]);
    }

    /**
     * Create a new Personal Access Token for the authenticated user.
     */
    public function _create(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status'  => 0,
                'message' => 'Unauthorized',
                'data'    => []
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => -2,
                'message' => 'Validation Failed',
                'data'    => $validator->messages()
            ]);
        }

        $tokenName = $request->input('name');
        $token = $user->createToken($tokenName);

        return response()->json([
            'status'  => 1,
            'message' => 'Token created successfully',
            'data'    => [
                'token'      => $token->accessToken,
                'plain_text' => $token->plainTextToken
            ]
        ]);
    }

    /**
     * Revoke/Delete a personal access token.
     */
    public function _delete(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status'  => 0,
                'message' => 'Unauthorized',
            ], 401);
        }

        $tokenId = $request->input('id');

        if (!$tokenId) {
            return response()->json([
                'status'  => 0,
                'message' => 'Token ID is required',
            ]);
        }

        $deleted = $user->tokens()->where('id', $tokenId)->delete();

        if ($deleted) {
            return response()->json([
                'status'  => 1,
                'message' => 'Token revoked successfully'
            ]);
        }

        return response()->json([
            'status'  => 0,
            'message' => 'Token not found or could not be revoked'
        ]);
    }
}
