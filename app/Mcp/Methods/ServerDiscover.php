<?php

declare(strict_types=1);

namespace App\Mcp\Methods;

use Laravel\Mcp\Server\Contracts\Methods\Method;
use Laravel\Mcp\Server\ServerContext;
use Laravel\Mcp\Server\Transport\JsonRpcRequest;
use Laravel\Mcp\Server\Transport\JsonRpcResponse;

class ServerDiscover implements Method
{
    /**
     * Handle the JSON-RPC server/discover request (MCP 2026-07-28 stateless spec).
     */
    public function handle(JsonRpcRequest $request, ServerContext $context): JsonRpcResponse
    {
        $serverInfo = [
            'name'    => $context->serverName,
            'version' => $context->serverVersion,
        ];

        return JsonRpcResponse::create($request->id, [
            'resultType'        => 'complete',
            'supportedVersions' => array_values(array_unique(array_merge(
                ['2026-07-28'],
                $context->supportedProtocolVersions,
                ['2025-06-18', '2025-03-26', '2024-11-25', '2024-11-05', '2024-10-07']
            ))),
            'capabilities'      => $context->serverCapabilities,
            'serverInfo'        => $serverInfo,
            '_meta'             => [
                'io.modelcontextprotocol/serverInfo' => $serverInfo,
            ],
            'instructions'      => $context->instructions,
            'ttlMs'             => 3600000,
            'cacheScope'        => 'public',
        ]);
    }
}
