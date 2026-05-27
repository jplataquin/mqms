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
        $timestamp = $request->header('X-TIMESTAMP');
        $signature = $request->header('X-SIGNATURE');

        if (!$apiKey || !$timestamp || !$signature) {
            return response()->json([
                'status'  => 0,
                'message' => 'Unauthorized: Missing required headers (X-API-KEY, X-TIMESTAMP, X-SIGNATURE).',
            ], 401);
        }

        // Validate timestamp (within 5 minutes to prevent replay attacks)
        $tolerance = 300; 
        if (abs(time() - (int)$timestamp) > $tolerance) {
            return response()->json([
                'status'  => 0,
                'message' => 'Unauthorized: Request timestamp has expired.',
            ], 401);
        }

        $credential = ApiCredential::where('api_key', $apiKey)->first();

        if (!$credential) {
            return response()->json([
                'status'  => 0,
                'message' => 'Unauthorized: Invalid API Key.',
            ], 401);
        }

        // Reconstruct payload: Method + Path + Timestamp + Raw Body (if any)
        $method = $request->method();
        $path = $request->path();
        $body = $request->getContent();
        
        $payload = $method . $path . $timestamp . $body;
        
        $expectedSignature = hash_hmac('sha256', $payload, $credential->secret_key);

        if (!hash_equals($expectedSignature, $signature)) {
            return response()->json([
                'status'  => 0,
                'message' => 'Unauthorized: Invalid Signature.',
            ], 401);
        }

        // Optional: Attach the credential to the request for later use
        $request->merge(['api_credential' => $credential]);

        return $next($request);
    }
}
