<?php

declare(strict_types=1);

namespace App\Mcp\Methods;

use App\Mcp\McpModelRegistry;
use Illuminate\Support\ItemNotFoundException;
use InvalidArgumentException;
use Laravel\Mcp\Server\Contracts\Methods\Method;
use Laravel\Mcp\Server\Resources\Content\Text;
use Laravel\Mcp\Server\Resources\ResourceResult;
use Laravel\Mcp\Server\ServerContext;
use Laravel\Mcp\Server\Transport\JsonRpcRequest;
use Laravel\Mcp\Server\Transport\JsonRpcResponse;

class CustomReadResource implements Method
{
    public function handle(JsonRpcRequest $request, ServerContext $context): JsonRpcResponse
    {
        $uri = $request->get('uri');

        if (is_null($uri)) {
            throw new InvalidArgumentException('Missing required parameter: uri');
        }

        // Check if URI matches pattern: mqms://models/{model}/{id}
        if (preg_match('#^mqms://models/([a-z0-9_-]+)/([0-9]+)$#i', $uri, $matches)) {
            $modelName = $matches[1];
            $id = (int) $matches[2];

            if (! McpModelRegistry::isAllowed($modelName)) {
                throw new ItemNotFoundException("Model '{$modelName}' is not allowed or does not exist.");
            }

            $modelClass = McpModelRegistry::getModelClass($modelName);
            $record = $modelClass::find($id);

            if (! $record) {
                throw new ItemNotFoundException("Record not found for {$modelName} with ID {$id}.");
            }

            $cleanData = McpModelRegistry::sanitize($record);
            $json = json_encode($cleanData, JSON_PRETTY_PRINT);

            return new JsonRpcResponse($request->id, [
                'contents' => [
                    [
                        'uri' => $uri,
                        'mimeType' => 'application/json',
                        'text' => $json,
                    ],
                ],
            ]);
        }

        // Fall back to registered static resources
        $resource = $context->resources()->first(fn ($r) => $r->uri() === $uri);
        if (is_null($resource)) {
            throw new ItemNotFoundException("Resource not found for URI: {$uri}");
        }

        return new JsonRpcResponse(
            $request->id,
            $resource->handle()->toArray(),
        );
    }
}
