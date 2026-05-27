<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * MQMS API Client (Reference Implementation)
 * 
 * This class is designed to be dropped into any Laravel application to
 * communicate with the MQMS Third-Party API.
 * 
 * Instructions:
 * 1. Copy this file to app/Services/MqmsApiClient.php in your target application.
 * 2. Add MQMS_API_BASE_URL, MQMS_API_KEY, and MQMS_API_SECRET_KEY to your .env file.
 */
class MqmsApiClient
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $secretKey;

    public function __construct()
    {
        // Pull configuration from .env
        $this->baseUrl   = rtrim(env('MQMS_API_BASE_URL'), '/') . '/';
        $this->apiKey    = env('MQMS_API_KEY');
        $this->secretKey = env('MQMS_API_SECRET_KEY');
    }

    /**
     * Core request handler that manages the HMAC signature.
     */
    protected function request(string $method, string $endpoint, array $data = [])
    {
        $timestamp = time();
        $method    = strtoupper($method);
        
        // Path used for signature must match the server's expectation (api/call/...)
        $path = 'api/call/' . ltrim($endpoint, '/');
        
        // Prepare the body for the signature
        $body = $method === 'GET' ? '' : json_encode($data);
        
        // Payload: METHOD + PATH + TIMESTAMP + BODY
        $payload   = $method . $path . $timestamp . $body;
        $signature = hash_hmac('sha256', $payload, $this->secretKey);

        $request = Http::withHeaders([
            'X-API-KEY'   => $this->apiKey,
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => $signature,
            'Accept'      => 'application/json',
        ]);

        $url = $this->baseUrl . $endpoint;

        $response = $method === 'GET' 
            ? $request->get($url, $data) 
            : $request->withBody($body, 'application/json')->send($method, $url);

        return $response->json();
    }

    /* --- Dedicated API Methods --- */

    /**
     * List projects.
     * Supported filters: page, limit, query, status, order_by, order
     */
    public function getProjects(array $filters = [])
    {
        return $this->request('GET', 'projects', $filters);
    }

    /**
     * List sections.
     * Supported filters: page, limit, query, project_id, order_by, order
     */
    public function getSections(array $filters = [])
    {
        return $this->request('GET', 'sections', $filters);
    }

    /**
     * List contract items.
     * Supported filters: page, limit, query, section_id, order_by, order
     */
    public function getContractItems(array $filters = [])
    {
        return $this->request('GET', 'contract_items', $filters);
    }

    /**
     * List materials.
     * Supported filters: page, limit, query, material_group_id, order_by, order
     */
    public function getMaterials(array $filters = [])
    {
        return $this->request('GET', 'materials', $filters);
    }

    /**
     * List suppliers.
     * Supported filters: page, limit, query, order_by, order
     */
    public function getSuppliers(array $filters = [])
    {
        return $this->request('GET', 'suppliers', $filters);
    }
}
