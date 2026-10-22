<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\McpModelRegistry;
use App\Mcp\Methods\CustomReadResource;
use App\Mcp\Resources\DynamicModelResource;
use App\Mcp\Tools\GetRecordTool;
use App\Mcp\Tools\ListRecordsTool;
use App\Mcp\Tools\SearchRecordsTool;
use Laravel\Mcp\Server;

class MqmsServer extends Server
{
    public string $serverName = 'MQMS MCP Server';

    public string $serverVersion = '1.0.0';

    public string $instructions = 'Provides HTTP MCP access to MQMS models (excluding roles, access codes, credentials, and user passwords). Allows reading records as resources and querying via get_record, list_records, and search_records tools.';

    public array $tools = [
        GetRecordTool::class,
        ListRecordsTool::class,
        SearchRecordsTool::class,
    ];

    public array $resources = [];

    public array $prompts = [];

    public function boot(): void
    {
        // Custom resource reader to support dynamic URI patterns like mqms://models/{model}/{id}
        $this->addMethod('resources/read', CustomReadResource::class);

        // Register each allowed model as an MCP resource overview
        foreach (McpModelRegistry::getAllowedModelKeys() as $modelKey) {
            $this->addResource(new DynamicModelResource($modelKey));
        }
    }
}
