<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\McpModelRegistry;
use App\Mcp\Methods\CustomReadResource;
use App\Mcp\Resources\DynamicModelResource;
use App\Mcp\Tools\GetMaterialPriceStatsTool;
use App\Mcp\Tools\GetRecordTool;
use App\Mcp\Tools\ListComponentItemsTool;
use App\Mcp\Tools\ListComponentsTool;
use App\Mcp\Tools\ListContractItemsTool;
use App\Mcp\Tools\ListProjectsTool;
use App\Mcp\Tools\ListRecordsTool;
use App\Mcp\Tools\ListSectionsTool;
use App\Mcp\Tools\SearchRecordsTool;
use Laravel\Mcp\Server;

class MqmsServer extends Server
{
    public string $serverName = 'MQMS MCP Server';

    public string $serverVersion = '1.0.0';

    public string $instructions = 'Provides HTTP MCP access to MQMS models (excluding roles, access codes, credentials, and user passwords). Allows reading records as resources and querying via specialized WBS tools (list_projects, list_sections, list_contract_items, list_components, list_component_items), get_record, list_records, search_records, and get_material_price_stats tools.';

    public int $defaultPaginationLength = 50;

    public int $maxPaginationLength = 100;

    public array $tools = [
        GetRecordTool::class,
        ListRecordsTool::class,
        SearchRecordsTool::class,
        GetMaterialPriceStatsTool::class,
        ListProjectsTool::class,
        ListSectionsTool::class,
        ListContractItemsTool::class,
        ListComponentsTool::class,
        ListComponentItemsTool::class,
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
