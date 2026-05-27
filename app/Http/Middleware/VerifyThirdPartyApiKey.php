<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiCredential;

class VerifyThirdPartyApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-KEY');
        $secretKey = $request->header('X-SECRET-KEY');

        if (!$apiKey || !$secretKey) {
            return response()->json([
                'status'  => 0,
                'message' => 'Unauthorized: API Key and Secret Key are required.',
            ], 401);
        }

        $credential = ApiCredential::where('api_key', $apiKey)
                                    ->where('secret_key', $secretKey)
                                    ->first();

        if (!$credential) {
            return response()->json([
                'status'  => 0,
                'message' => 'Unauthorized: Invalid API Key or Secret Key.',
            ], 401);
        }

        // Optional: Attach the credential to the request for later use
        $request->merge(['api_credential' => $credential]);

        return $next($request);
    }
}
