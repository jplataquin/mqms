<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestApiAuth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:test-auth 
                            {api_key? : The API Key to test} 
                            {secret_key? : The Secret Key to test} 
                            {--base_url= : The base URL of the MQMS application}
                            {--path=api/call/projects : The API path to test}
                            {--method=GET : HTTP method}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test 3rd party API authentication by generating a signature and making a request.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $apiKey = $this->argument('api_key') ?? env('MQMS_API_KEY');
        $secretKey = $this->argument('secret_key') ?? env('MQMS_API_SECRET_KEY');
        $path = ltrim($this->option('path'), '/');
        $method = strtoupper($this->option('method'));

        if (!$apiKey || !$secretKey) {
            $this->error('API Key and Secret Key are required. Provide them as arguments or set them in .env');
            return 1;
        }

        $this->info("--- API Diagnostic Start ---");
        $this->info("Testing API Key: " . $apiKey);
        
        // 1. Check Database
        $this->comment("1. Checking Database...");
        $credential = \App\Models\ApiCredential::where('api_key', $apiKey)->first();

        if (!$credential) {
            $this->error("FAILED: API Key not found in 'api_credentials' table.");
            
            // Check if it's soft deleted
            $softDeleted = \App\Models\ApiCredential::onlyTrashed()->where('api_key', $apiKey)->first();
            if ($softDeleted) {
                $this->warn("Note: This API Key exists but is SOFT DELETED (deleted_at: {$softDeleted->deleted_at}).");
            }
            return 1;
        }
        $this->info("SUCCESS: API Key found. Name: {$credential->name}");

        // 2. Validate Secret Key
        $this->comment("2. Validating Secret Key...");
        if ($credential->secret_key !== $secretKey) {
            $this->error("FAILED: Provided Secret Key does not match the one in the database.");
            $this->line("Database Secret Key starts with: " . substr($credential->secret_key, 0, 8) . "...");
            return 1;
        }
        $this->info("SUCCESS: Secret Key matches.");

        // 3. Simulate Signature Generation (Same logic as middleware)
        $this->comment("3. Simulating Signature Generation...");
        $timestamp = time();
        $body = ''; 
        $payload = $method . $path . $timestamp . $body;
        $expectedSignature = hash_hmac('sha256', $payload, $secretKey);

        $this->line("Payload Used: " . $payload);
        $this->line("Timestamp: " . $timestamp);
        $this->line("Generated Signature: " . $expectedSignature);

        // 4. Diagnostic Summary
        $this->newLine();
        $this->info("--- Diagnostic Summary ---");
        $this->info("The credentials provided are VALID and present in the database.");
        $this->info("If you are still getting 'unauthenticated', please ensure:");
        $this->line("1. Your client is using the EXACT same payload concatenation: Method + Path + Timestamp + Body");
        $this->line("   Current Path being tested: " . $path);
        $this->line("2. Your client is sending the 'X-TIMESTAMP' header matching the one used in the signature.");
        $this->line("3. The server time and client time are synchronized (5-minute tolerance).");
        $this->line("   Current Server Time (UTC): " . date('Y-m-d H:i:s') . " (Timestamp: " . time() . ")");
        $this->line("4. No leading/trailing slashes in the path are causing mismatches.");

        return 0;
    }
}
