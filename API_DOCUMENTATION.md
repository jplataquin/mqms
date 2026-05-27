# MQMS Third-Party API Documentation

This document provides instructions for external systems to integrate with the MQMS (Material and Quantity Management System) via its REST API.

## 1. Authentication

All third-party API requests use a secure HMAC (Hash-based Message Authentication Code) signature for authentication. The **Secret Key** is never sent in the request.

### Required Headers
| Header | Description |
| :--- | :--- |
| `X-API-KEY` | Your unique API Key generated within MQMS. |
| `X-TIMESTAMP` | Current Unix timestamp (in seconds). Must be within 5 minutes of server time. |
| `X-SIGNATURE` | The HMAC-SHA256 signature generated using your Secret Key. |

### Signature Generation
To generate the `X-SIGNATURE`, you must:
1. Create a **Payload String** by concatenating: `HTTP_METHOD + PATH + TIMESTAMP + REQUEST_BODY`.
   - `HTTP_METHOD`: Uppercase method (e.g., `GET`, `POST`).
   - `PATH`: The full path after the domain (e.g., `api/call/projects`).
   - `TIMESTAMP`: The same Unix timestamp sent in the `X-TIMESTAMP` header.
   - `REQUEST_BODY`: The raw request body (if any). If there is no body, use an empty string.
2. Hash the payload string using the **HMAC-SHA256** algorithm with your **Secret Key** as the hashing key.
3. The resulting hex-encoded string is your signature.

### Obtaining Credentials
1. Log in to the MQMS web application.
2. Navigate to **API Credentials**.
3. Click **Create** to generate a new set of credentials.
4. Copy both the **API Key** and **Secret Key**. 

---

## 2. URL Structure

All third-party endpoints follow a standard URL pattern:

`{BASE_URL}/api/call/{endpoint}`

Example: `https://mqms.example.com/api/call/projects`

---

## 3. Available Endpoints

### List Projects
Retrieves a list of projects managed in the system.

*   **URL**: `/api/call/projects`
*   **Method**: `GET`
*   **Query Parameters**:
    *   `page` (int, default: 1): The page number for pagination.
    *   `limit` (int, default: 10): Number of records per page.
    *   `query` (string, optional): Filter by project name.

#### Example Request (PHP)
```php
$apiKey = 'your_api_key';
$secretKey = 'your_secret_key';
$timestamp = time();
$method = 'GET';
$path = 'api/call/projects';
$body = ''; // Empty for GET

$payload = $method . $path . $timestamp . $body;
$signature = hash_hmac('sha256', $payload, $secretKey);

$headers = [
    "X-API-KEY: $apiKey",
    "X-TIMESTAMP: $timestamp",
    "X-SIGNATURE: $signature",
    "Accept: application/json"
];

// ... execute curl request to https://mqms.example.com/api/call/projects
```

#### Example Request (cURL)
```bash
# Note: Signature must be pre-calculated
curl -X GET "https://mqms.example.com/api/call/projects" \
     -H "X-API-KEY: your_api_key" \
     -H "X-TIMESTAMP: 1716824400" \
     -H "X-SIGNATURE: generated_hmac_signature" \
     -H "Accept: application/json"
```

---

## 4. Response Status Codes

| HTTP Code | status field | Meaning |
| :--- | :--- | :--- |
| 200 OK | 1 | Request successful. |
| 401 Unauthorized | 0 | Authentication failed (invalid signature, expired timestamp, etc.). |
| 404 Not Found | 0 | Endpoint does not exist. |
| 500 Server Error | 0 | Internal server error. |
