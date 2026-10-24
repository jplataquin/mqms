<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\McpModelRegistry;
use App\Mcp\Methods\CustomReadResource;
use App\Mcp\Resources\DynamicModelResource;
use App\Mcp\Tools\GetMaterialPriceStatsTool;
use App\Mcp\Tools\GetRecordTool;
use App\Mcp\Tools\LevelOneBudgetDiagnosticTool;
use App\Mcp\Tools\ListAccomplishmentsTool;
use App\Mcp\Tools\ListCommentsTool;
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
use App\Mcp\Tools\ListUsersTool;
use App\Mcp\Tools\RunLevelOneBudgetDiagnosticTool;
use App\Mcp\Tools\RunLevelTwoBudgetDiagnosticTool;
use App\Mcp\Tools\LevelTwoBudgetDiagnosticTool;
use App\Mcp\Tools\SearchRecordsTool;
use Laravel\Mcp\Server;

class MqmsServer extends Server
{
    public string $serverName = 'MQMS MCP Server';

    public string $serverVersion = '1.0.0';

    public string $instructions = 'Provides HTTP MCP access to MQMS models (excluding roles, access codes, credentials, and user passwords). Allows reading records and protocol diagnostics as resources and querying via specialized WBS, Budget/Materials, Procurement/Workflow, Partners/Execution, and System/Misc tools, get_record, list_records, search_records, get_material_price_stats, run_level_one_budget_diagnostic, and run_level_two_budget_diagnostic tools.

Important Core Hierarchies & Lifecycle Flows:
1. Work Breakdown Structure (WBS):
   Project -> Section -> Contract Item -> Component -> Component Item -> Material Quantity.
   Traverse data following this parent-child hierarchy to ensure accurate relationships.

2. Procurement & Receiving Lifecycle:
   Material Quantity Request (MQR) -> Purchase Order (PO) -> Received Quantities (PO Received).
   - A single approved MQR may have multiple Purchase Orders (e.g., split orders, different suppliers, partial procurement).
   - A single Purchase Order may have multiple Received records (partial deliveries until fulfilled).
   - Item-level linkage: MaterialQuantityRequestItem -> PurchaseOrderItem -> PurchaseOrderReceivedItem.';

    public int $defaultPaginationLength = 50;

    public int $maxPaginationLength = 100;

    public array $supportedProtocolVersion = [
        '2025-06-18',
        '2025-03-26',
        '2024-11-25',
        '2024-11-05',
        '2024-10-07',
    ];

    public array $tools = [
        GetRecordTool::class,
        ListRecordsTool::class,
        SearchRecordsTool::class,
        GetMaterialPriceStatsTool::class,
        RunLevelOneBudgetDiagnosticTool::class,
        LevelOneBudgetDiagnosticTool::class,
        RunLevelTwoBudgetDiagnosticTool::class,
        LevelTwoBudgetDiagnosticTool::class,
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
        ListUsersTool::class,
        ListCommentsTool::class,
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

    /**
     * Handle incoming JSON-RPC messages with graceful protocol version negotiation.
     */
    public function handle(string $rawMessage)
    {
        try {
            $req = \Laravel\Mcp\Server\Transport\JsonRpcRequest::fromJson($rawMessage);
            $requested = $req->params['protocolVersion'] ?? null;
            if ($requested && ! in_array($requested, $this->supportedProtocolVersion, true)) {
                $this->supportedProtocolVersion[] = (string) $requested;
            }
        } catch (\Throwable $e) {
            // Let parent handle any JSON parse or format errors
        }

        return parent::handle($rawMessage);
    }
}
