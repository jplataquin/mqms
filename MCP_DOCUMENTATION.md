# MQMS Model Context Protocol (MCP) Server Documentation

This document provides complete technical documentation for the Model Context Protocol (MCP) server integrated into MQMS (Material and Quantity Management System).

The MCP server enables external AI assistants, IDEs, and autonomous agents (such as Gemini CLI, Claude Desktop, Cursor, and custom LLM workflows) to securely inspect data models, search database records, read resources, and execute specialized domain calculations.

---

## 1. Overview & Architecture

MQMS implements the [Model Context Protocol](https://modelcontextprotocol.io/) using `laravel/mcp`. The server exposes a standardized JSON-RPC 2.0 interface that bridges external AI clients to the MQMS application layer and Eloquent ORM.

### Server Identity
- **Server Name:** `MQMS MCP Server`
- **Server Version:** `1.0.0`
- **Protocol Version:** `2024-11-05` (JSON-RPC 2.0)
- **Primary Class:** `\App\Mcp\Servers\MqmsServer`
- **Model Registry:** `\App\Mcp\McpModelRegistry`

### Key Capabilities
1. **Dynamic Resource Overview:** Inspect database schema metadata, record counts, and access templates via `mqms://models/{model}`.
2. **Direct Record Retrieval via URI:** Direct resource reading via `mqms://models/{model}/{id}` through a custom MCP method handler.
3. **Structured Query Tools:**
   - `get_record`: Look up any permitted model by ID.
   - `list_records`: Paginated, ordered queries with column validation.
   - `search_records`: Multi-field fuzzy search across common text columns and primary keys.
   - `get_material_price_stats`: Domain analytics aggregating approved canvass pricing from both standard and direct procurement pipelines.
4. **Strict Security Boundaries:** Whitelist of allowed entities, explicit denial of RBAC and API credential tables, and automatic scrubbing of passwords and security tokens.

---

## 2. Endpoints & Transports

### 1. Authenticated Web / HTTP Endpoint (Production)
The primary endpoint for remote AI clients and integrations. Protected by Laravel Sanctum.

- **URL:** `{BASE_URL}/api/mcp`
- **Method:** `POST`
- **Content-Type:** `application/json`
- **Authentication:** `Bearer <Personal Access Token>`
- **Rate Limit:** 200 requests per minute per user/IP.

### 2. Internal / Local Web Endpoint
Registered via `routes/ai.php` and loaded under the `/mcp` prefix by the package service provider.

- **URL:** `{BASE_URL}/mcp`
- **Method:** `POST`
- **Content-Type:** `application/json`

### 3. Console & Debugging Tools
The server can also be inspected using Laravel Artisan commands:
- **Interactive Inspector:**
  ```bash
  php artisan mcp:inspector
  ```
- **Local Server Execution:**
  ```bash
  php artisan mcp:start <handle>
  ```

---

## 3. Authentication & Personal Access Tokens

Requests to `/api/mcp` must supply a valid Laravel Sanctum Personal Access Token via the standard `Authorization` header:

```http
Authorization: Bearer <your_token_here>
```

### Obtaining a Token

#### Option A: Web User Interface
1. Log in to the MQMS application.
2. Open the navigation bar and select **AI / MCP -> Tokens** (URL: `/personal_access_tokens`).
3. Click **Generate New Token** (`/personal_access_tokens/create`).
4. Enter a descriptive token name (e.g. `Gemini CLI - Workstation`, `Claude Desktop`).
5. Click **Create Token**.
6. Copy the plain-text token immediately (it will only be displayed once).

#### Option B: HTTP API (for programmatic onboarding)
Authenticated web sessions can provision tokens via the API:

```http
POST /api/personal_access_tokens/create HTTP/1.1
Content-Type: application/json

{
  "name": "Gemini CLI"
}
```

**Response:**
```json
{
  "status": 1,
  "message": "Token created successfully",
  "data": {
    "token": {
      "name": "Gemini CLI",
      "abilities": ["*"],
      "tokenable_id": 1,
      "tokenable_type": "App\\Models\\User",
      "updated_at": "2026-10-23T12:00:00.000000Z",
      "created_at": "2026-10-23T12:00:00.000000Z",
      "id": 15
    },
    "plain_text": "15|7vK8O...plainTextSecret..."
  }
}
```

### Revoking a Token
Tokens can be revoked at any time via the **AI / MCP -> Tokens** interface or by sending:

```http
POST /api/personal_access_tokens/delete HTTP/1.1
Content-Type: application/json

{
  "id": 15
}
```

---

## 4. Security & Data Governance

MQMS enforces strict data boundaries managed centrally by `App\Mcp\McpModelRegistry`:

### Allowed Models Whitelist (28 Models)
Only the following models can be accessed via MCP tools and resources:

| Category | Allowed Models |
| :--- | :--- |
| **Projects & Structure** | `project`, `section`, `contract_item`, `component`, `component_item` |
| **Materials & Registry** | `material_group`, `material_item`, `material_quantity`, `unit` |
| **Material Requests (MQR)** | `material_quantity_request`, `material_quantity_request_item`, `direct_material_quantity_request`, `direct_material_quantity_request_item` |
| **Canvasses & Quotes** | `material_canvass`, `direct_material_canvass` |
| **Purchase Orders (PO)** | `purchase_order`, `purchase_order_item`, `purchase_order_received`, `purchase_order_received_item`, `direct_purchase_order`, `direct_purchase_order_item`, `direct_purchase_order_received`, `direct_purchase_order_received_item` |
| **Vendors & Terms** | `supplier`, `payment_term` |
| **Site Tracking & Operations** | `accomplishment`, `manpower_registry`, `coupon`, `comment` |
| **Users** | `user` *(strictly sanitized)* |

### Forbidden Models Blacklist
The following models and aliases are **strictly prohibited** and rejected at runtime:
- `role`, `roles`
- `access_code`, `access_codes`
- `role_access_code`, `role_access_codes`
- `user_role`, `user_roles`
- `api_credential`, `api_credentials`, `apicredential`, `apicredentials`

Any attempt to request a forbidden model results in an immediate MCP error response (`isError: true`).

### Sensitive Field Sanitization
Whenever an Eloquent model is serialized by the MCP layer (via tools or resources), the `McpModelRegistry::sanitize()` routine automatically strips the following fields:
- `password`
- `remember_token`
- `secret_key`
- `api_key`
- `key`
- `secret`

### SQL Injection Protection
When sorting records using `list_records`, the requested `order_by` column is validated directly against the database schema builder (`$schema->hasColumn($table, $orderBy)`). Non-existent or invalid columns automatically fall back to the primary key.

---

## 5. Available MCP Tools

The server provides four core tools registered in `MqmsServer`:

```
1. get_record
2. list_records
3. search_records
4. get_material_price_stats
```

---

### Tool 1: `get_record`
Retrieves a single record by its model name and primary key ID.

#### Parameters
| Field | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `model` | `string` | **Yes** | The allowed model name (e.g. `project`, `material_item`, `supplier`). |
| `id` | `integer` | **Yes** | The primary key ID of the record. |

#### Example Request
```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "method": "tools/call",
  "params": {
    "name": "get_record",
    "arguments": {
      "model": "project",
      "id": 1
    }
  }
}
```

#### Example Response
```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "result": {
    "content": [
      {
        "type": "text",
        "text": "{\n    \"id\": 1,\n    \"name\": \"City Highway Expansion\",\n    \"status\": \"ACTV\",\n    \"created_by\": 1,\n    \"created_at\": \"2026-01-15T08:30:00.000000Z\",\n    \"updated_at\": \"2026-03-10T14:22:00.000000Z\"\n}"
      }
    ],
    "isError": false
  }
}
```

---

### Tool 2: `list_records`
Retrieves a paginated list of records for a given model with configurable sorting.

#### Parameters
| Field | Type | Required | Default | Description |
| :--- | :--- | :--- | :--- | :--- |
| `model` | `string` | **Yes** | — | The model name to list (e.g. `material_item`, `supplier`). |
| `page` | `integer` | No | `1` | The page number to retrieve (minimum `1`). |
| `limit` | `integer` | No | `15` | Records per page (maximum `100`). |
| `order_by` | `string` | No | `'id'` | Column to sort by (verified against database schema). |
| `order` | `string` | No | `'DESC'` | Sort order: `'ASC'` or `'DESC'`. |

#### Example Request
```json
{
  "jsonrpc": "2.0",
  "id": 2,
  "method": "tools/call",
  "params": {
    "name": "list_records",
    "arguments": {
      "model": "supplier",
      "page": 1,
      "limit": 2,
      "order_by": "id",
      "order": "ASC"
    }
  }
}
```

#### Example Response
```json
{
  "jsonrpc": "2.0",
  "id": 2,
  "result": {
    "content": [
      {
        "type": "text",
        "text": "{\n    \"model\": \"supplier\",\n    \"page\": 1,\n    \"limit\": 2,\n    \"total\": 12,\n    \"total_pages\": 6,\n    \"records\": [\n        {\n            \"id\": 1,\n            \"name\": \"Apex Building Supplies\",\n            \"address\": \"Iloilo City\",\n            \"primary_contact_no\": \"09111111111\"\n        },\n        {\n            \"id\": 2,\n            \"name\": \"Beacon Trade Depot\",\n            \"address\": \"Pavia\",\n            \"primary_contact_no\": \"09222222222\"\n        }\n    ]\n}"
      }
    ],
    "isError": false
  }
}
```

---

### Tool 3: `search_records`
Performs a keyword search across model text columns (e.g. `name`, `title`, `description`, `remarks`, `brand`, `code`, `plate_no`, `specification_unit_packaging`). If the query is numeric, also matches the primary key `id`.

#### Parameters
| Field | Type | Required | Default | Description |
| :--- | :--- | :--- | :--- | :--- |
| `model` | `string` | **Yes** | — | The target model name. |
| `query` | `string` | **Yes** | — | Search string or keyword. Cannot be empty. |
| `page` | `integer` | No | `1` | Page number. |
| `limit` | `integer` | No | `15` | Records per page (maximum `100`). |

#### Example Request
```json
{
  "jsonrpc": "2.0",
  "id": 3,
  "method": "tools/call",
  "params": {
    "name": "search_records",
    "arguments": {
      "model": "material_item",
      "query": "Portland Cement",
      "page": 1,
      "limit": 5
    }
  }
}
```

#### Example Response
```json
{
  "jsonrpc": "2.0",
  "id": 3,
  "result": {
    "content": [
      {
        "type": "text",
        "text": "{\n    \"model\": \"material_item\",\n    \"query\": \"Portland Cement\",\n    \"searchable_fields\": [\n        \"name\",\n        \"description\",\n        \"brand\",\n        \"specification_unit_packaging\"\n    ],\n    \"page\": 1,\n    \"limit\": 5,\n    \"total\": 1,\n    \"total_pages\": 1,\n    \"records\": [\n        {\n            \"id\": 42,\n            \"name\": \"Type 1P Portland Cement\",\n            \"brand\": \"Holcim\",\n            \"specification_unit_packaging\": \"40kg [ Bag ]\",\n            \"material_group_id\": 3\n        }\n    ]\n}"
      }
    ],
    "isError": false
  }
}
```

---

### Tool 4: `get_material_price_stats`
Calculates comprehensive price statistics for a specific material item across both **standard** (`material_canvass`) and **direct** (`direct_material_canvass`) procurement pipelines. Only **approved** canvasses (`status = 'APRV'`) are included.

#### Parameters
| Field | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `material_item_id` | `integer` | **Yes** | The primary key ID of the `material_item`. |
| `supplier_id` | `integer` | No | Optional supplier ID to isolate pricing from a specific vendor. |
| `date_from` | `string` | No | Optional start date (`YYYY-MM-DD`) filtering canvasses approved on or after this date. |
| `date_to` | `string` | No | Optional end date (`YYYY-MM-DD`) filtering canvasses approved on or before this date. |

#### Returned Statistics
- `average_price`: Rounded to 2 decimal places.
- `min_price`: Lowest approved canvass unit price.
- `max_price`: Highest approved canvass unit price.
- `latest_price`: Unit price of the most recently approved quote.
- `latest_approved_at`: Timestamp of latest approval.
- `latest_source`: Pipeline source (`'standard'` or `'direct'`).
- `supplier_breakdown`: Per-vendor statistics (canvass count, min, max, average) sorted by quotation frequency.

#### Example Request
```json
{
  "jsonrpc": "2.0",
  "id": 4,
  "method": "tools/call",
  "params": {
    "name": "get_material_price_stats",
    "arguments": {
      "material_item_id": 42,
      "date_from": "2026-01-01",
      "date_to": "2026-10-23"
    }
  }
}
```

#### Example Response
```json
{
  "jsonrpc": "2.0",
  "id": 4,
  "result": {
    "content": [
      {
        "type": "text",
        "text": "{\n    \"material_item_id\": 42,\n    \"material_name\": \"Type 1P Portland Cement (40kg [ Bag ])\",\n    \"filters_applied\": {\n        \"supplier_id\": null,\n        \"date_from\": \"2026-01-01\",\n        \"date_to\": \"2026-10-23\"\n    },\n    \"total_approved_canvasses\": 3,\n    \"stats\": {\n        \"average_price\": 230,\n        \"min_price\": 200,\n        \"max_price\": 260,\n        \"latest_price\": 230,\n        \"latest_approved_at\": \"2026-06-01 14:00:00\",\n        \"latest_source\": \"direct\"\n    },\n    \"supplier_breakdown\": [\n        {\n            \"supplier_id\": 1,\n            \"supplier_name\": \"Apex Building Supplies\",\n            \"canvass_count\": 2,\n            \"min_price\": 200,\n            \"max_price\": 230,\n            \"average_price\": 215\n        },\n        {\n            \"supplier_id\": 2,\n            \"supplier_name\": \"Beacon Trade Depot\",\n            \"canvass_count\": 1,\n            \"min_price\": 260,\n            \"max_price\": 260,\n            \"average_price\": 260\n        }\n    ]\n}"
      }
    ],
    "isError": false
  }
}
```

---

## 6. MCP Resources

The MCP server exposes database models as resources that can be indexed and read by AI agents.

### 1. Model Overview Resources (`mqms://models/{model}`)
Each permitted model is exposed as a top-level resource detailing:
- Underlying database table name.
- Total existing record count.
- List of safe, readable columns.
- Direct access URI template.

#### Reading a Model Overview Resource
**Request:**
```json
{
  "jsonrpc": "2.0",
  "id": 5,
  "method": "resources/read",
  "params": {
    "uri": "mqms://models/project"
  }
}
```

**Response:**
```json
{
  "jsonrpc": "2.0",
  "id": 5,
  "result": {
    "contents": [
      {
        "uri": "mqms://models/project",
        "mimeType": "application/json",
        "text": "{\n    \"model\": \"project\",\n    \"table\": \"projects\",\n    \"total_records\": 8,\n    \"columns\": [\n        \"id\",\n        \"name\",\n        \"status\",\n        \"created_by\",\n        \"updated_by\",\n        \"created_at\",\n        \"updated_at\"\n    ],\n    \"access_uri_template\": \"mqms://models/project/{id}\"\n}"
      }
    ]
  }
}
```

### 2. Direct Record Resources (`mqms://models/{model}/{id}`)
Handled via `\App\Mcp\Methods\CustomReadResource`, this allows any client to directly fetch a specific record as a resource without calling a tool.

#### Reading a Specific Record Resource
**Request:**
```json
{
  "jsonrpc": "2.0",
  "id": 6,
  "method": "resources/read",
  "params": {
    "uri": "mqms://models/supplier/1"
  }
}
```

**Response:**
```json
{
  "jsonrpc": "2.0",
  "id": 6,
  "result": {
    "contents": [
      {
        "uri": "mqms://models/supplier/1",
        "mimeType": "application/json",
        "text": "{\n    \"id\": 1,\n    \"name\": \"Apex Building Supplies\",\n    \"address\": \"Iloilo City\",\n    \"primary_contact_no\": \"09111111111\",\n    \"created_by\": 1,\n    \"created_at\": \"2026-02-01T10:00:00.000000Z\",\n    \"updated_at\": \"2026-02-01T10:00:00.000000Z\"\n}"
      }
    ]
  }
}
```

---

## 7. Standard JSON-RPC Methods Reference

The server implements standard MCP JSON-RPC 2.0 lifecycle methods:

### Handshake: `initialize`
```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "method": "initialize",
  "params": {
    "protocolVersion": "2024-11-05",
    "capabilities": {},
    "clientInfo": {
      "name": "my-client",
      "version": "1.0.0"
    }
  }
}
```

### Health Check: `ping`
```json
{
  "jsonrpc": "2.0",
  "id": 2,
  "method": "ping"
}
```

### Discovery: `tools/list`
Lists all available tools and their full input schemas.
```json
{
  "jsonrpc": "2.0",
  "id": 3,
  "method": "tools/list",
  "params": {}
}
```

### Discovery: `resources/list`
Lists all registered model overview resources (`mqms://models/*`).
```json
{
  "jsonrpc": "2.0",
  "id": 4,
  "method": "resources/list",
  "params": {}
}
```

---

## 8. Client Setup & Integration Guides

### 1. Gemini CLI Configuration
Add the server entry to your project's `.gemini/settings.json` or global configuration:

```json
{
  "mcpServers": {
    "mqms_server": {
      "url": "https://your-domain.com/api/mcp",
      "type": "http",
      "headers": {
        "Authorization": "Bearer <YOUR_PERSONAL_ACCESS_TOKEN>"
      }
    }
  }
}
```

### 2. Claude Desktop Configuration
For Claude Desktop (`claude_desktop_config.json`), if using direct HTTP streaming or an MCP proxy bridge (like `mcp-proxy` or `npx -y @modelcontextprotocol/server-fetch`):

```json
{
  "mcpServers": {
    "mqms": {
      "command": "npx",
      "args": [
        "-y",
        "mcp-proxy",
        "https://your-domain.com/api/mcp",
        "--header",
        "Authorization: Bearer <YOUR_PERSONAL_ACCESS_TOKEN>"
      ]
    }
  }
}
```

### 3. Cursor IDE / VS Code MCP Extensions
Configure your Cursor/VS Code MCP extension settings:

```json
{
  "mcpServers": {
    "mqms": {
      "url": "https://your-domain.com/api/mcp",
      "headers": {
        "Authorization": "Bearer <YOUR_PERSONAL_ACCESS_TOKEN>"
      }
    }
  }
}
```

### 4. cURL Direct Request Examples

#### Initialize
```bash
curl -X POST "https://your-domain.com/api/mcp" \
  -H "Authorization: Bearer <YOUR_PERSONAL_ACCESS_TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "id": 1,
    "method": "initialize",
    "params": {
      "protocolVersion": "2024-11-05",
      "capabilities": {},
      "clientInfo": { "name": "curl-test", "version": "1.0.0" }
    }
  }'
```

#### Call Tool (`get_record`)
```bash
curl -X POST "https://your-domain.com/api/mcp" \
  -H "Authorization: Bearer <YOUR_PERSONAL_ACCESS_TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "id": 2,
    "method": "tools/call",
    "params": {
      "name": "get_record",
      "arguments": {
        "model": "project",
        "id": 1
      }
    }
  }'
```

---

## 9. Error Handling & Troubleshooting

### Error Responses

#### 1. Authentication Failure (HTTP 401)
Occurs when the `Authorization` header is missing, malformed, or references an expired/revoked token.
```json
{
  "message": "Unauthenticated."
}
```

#### 2. Model Not Allowed / Forbidden
When a forbidden model (e.g. `role`, `api_credential`) or non-existent model is queried via a tool:
```json
{
  "jsonrpc": "2.0",
  "id": 3,
  "result": {
    "content": [
      {
        "type": "text",
        "text": "Model 'role' is not allowed or does not exist."
      }
    ],
    "isError": true
  }
}
```

#### 3. Record Not Found
When a valid model is requested with an ID that does not exist or has been soft-deleted:
```json
{
  "jsonrpc": "2.0",
  "id": 4,
  "result": {
    "content": [
      {
        "type": "text",
        "text": "Record not found for project with ID 9999."
      }
    ],
    "isError": true
  }
}
```

### Troubleshooting Checklist
1. **401 Unauthorized:** Verify that your Personal Access Token is active under **AI / MCP -> Tokens** (`/personal_access_tokens`).
2. **Missing Tools or Resources:** Verify that the request body conforms strictly to JSON-RPC 2.0 (`"jsonrpc": "2.0"` must be present).
3. **Rate Limiting (429 Too Many Requests):** By default, the API route group allows up to 200 requests per minute. Batch requests or throttle high-frequency loops accordingly.
4. **Soft-Deleted Records:** Standard queries filter out soft-deleted records. However, `get_material_price_stats` includes historical material items and suppliers with `withTrashed()` to maintain accurate audit calculations.
