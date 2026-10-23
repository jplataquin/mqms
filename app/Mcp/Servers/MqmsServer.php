<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\McpModelRegistry;
use App\Mcp\Methods\CustomReadResource;
use App\Mcp\Resources\DynamicModelResource;
use App\Mcp\Tools\GetMaterialPriceStatsTool;
use App\Mcp\Tools\GetRecordTool;
use App\Mcp\Tools\ListAccomplishmentsTool;
use App\Mcp\Tools\ListComponentItemsTool;
use App\Mcp\Tools\ListComponentsTool;
use App\Mcp\Tools\ListContractItemsTool;
use App\Mcp\Tools\ListCouponsTool;
use App\Mcp\Tools\ListManpowerRegistryTool;
use App\Mcp\Tools\ListMaterialCanvassesTool;
use App\Mcp\Tools\ListMaterialGroupsTool;
use App\Mcp\Tools\ListMaterialItemsTool;
use App\Mcp\Tools\ListMaterialQuantitiesTool;
use App\Mcp\Tools\ListMaterialQuantityRequestItemsTool;
use App\Mcp\Tools\ListMaterialQuantityRequestsTool;
use App\Mcp\Tools\ListPaymentTermsTool;
use App\Mcp\Tools\ListProjectsTool;
use App\Mcp\Tools\ListPurchaseOrderItemsTool;
use App\Mcp\Tools\ListPurchaseOrderReceivedItemsTool;
use App\Mcp\Tools\ListPurchaseOrderReceivedTool;
use App\Mcp\Tools\ListPurchaseOrdersTool;
use App\Mcp\Tools\ListRecordsTool;
use App\Mcp\Tools\ListSectionsTool;
use App\Mcp\Tools\ListSuppliersTool;
use App\Mcp\Tools\ListUnitsTool;
use App\Mcp\Tools\SearchRecordsTool;
use Laravel\Mcp\Server;

class MqmsServer extends Server
{
    public string $serverName = 'MQMS MCP Server';

    public string $serverVersion = '1.0.0';

    public string $instructions = 'Provides HTTP MCP access to MQMS models (excluding roles, access codes, credentials, and user passwords). Allows reading records as resources and querying via specialized WBS, Budget/Materials, Procurement/Workflow, and Partners/Execution tools, get_record, list_records, search_records, and get_material_price_stats tools.';

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
        ListMaterialQuantitiesTool::class,
        ListMaterialItemsTool::class,
        ListMaterialGroupsTool::class,
        ListUnitsTool::class,
        ListMaterialQuantityRequestsTool::class,
        ListMaterialQuantityRequestItemsTool::class,
        ListMaterialCanvassesTool::class,
        ListPurchaseOrdersTool::class,
        ListPurchaseOrderItemsTool::class,
        ListPurchaseOrderReceivedTool::class,
        ListPurchaseOrderReceivedItemsTool::class,
        ListSuppliersTool::class,
        ListPaymentTermsTool::class,
        ListManpowerRegistryTool::class,
        ListCouponsTool::class,
        ListAccomplishmentsTool::class,
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
