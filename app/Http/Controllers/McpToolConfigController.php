<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mcp\Servers\MqmsServer;
use App\Models\McpToolConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class McpToolConfigController extends Controller
{
    /**
     * Show the tool configurations list view.
     */
    public function list(): View
    {
        return view('mcp_tool_config/list');
    }

    /**
     * Show the edit view for a specific tool.
     */
    public function edit(string $name): View
    {
        $server = app(MqmsServer::class);
        $toolInstance = null;

        foreach ($server->tools as $toolClass) {
            $instance = app($toolClass);
            if ($instance->name() === $name) {
                $toolInstance = $instance;
                break;
            }
        }

        if (! $toolInstance) {
            abort(404, "MCP Tool '{$name}' not found.");
        }

        $defaultDescription = method_exists($toolInstance, 'defaultDescription')
            ? $toolInstance->defaultDescription()
            : $toolInstance->description();
        $config = McpToolConfig::where('tool_name', $name)->first();
        $customDescription = $config?->custom_description;

        return view('mcp_tool_config/edit', [
            'toolName'           => $name,
            'defaultDescription' => $defaultDescription,
            'customDescription'  => $customDescription,
            'config'             => $config,
        ]);
    }

    /**
     * Get JSON list of all MCP tools and their description configurations.
     */
    public function _list(Request $request): JsonResponse
    {
        $server = app(MqmsServer::class);
        $savedConfigs = McpToolConfig::all()->keyBy('tool_name');
        $tools = [];

        foreach ($server->tools as $toolClass) {
            $instance = app($toolClass);
            $name = $instance->name();
            $default = method_exists($instance, 'defaultDescription')
                ? $instance->defaultDescription()
                : $instance->description();
            $config = $savedConfigs->get($name);
            $custom = $config?->custom_description;
            $isCustomized = ($custom !== null && trim($custom) !== '');

            $tools[] = [
                'tool_name'           => $name,
                'default_description' => $default,
                'custom_description'  => $custom,
                'current_description' => $isCustomized ? trim($custom) : $default,
                'is_customized'       => $isCustomized,
                'updated_at'          => $config?->updated_at?->toIso8601String(),
                'updated_by'          => $config?->UpdatedByUser?->name ?? 'N/A',
            ];
        }

        usort($tools, fn ($a, $b) => strcmp($a['tool_name'], $b['tool_name']));

        return response()->json([
            'status'  => 1,
            'message' => '',
            'data'    => $tools,
        ]);
    }

    /**
     * Update or save a custom description for a tool.
     */
    public function _update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tool_name'          => 'required|string|max:255',
            'custom_description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => -2,
                'message' => 'Validation Failed',
                'data'    => $validator->messages(),
            ], 422);
        }

        $toolName = (string) $request->input('tool_name');
        $customDescription = $request->input('custom_description');

        // Check tool exists in registered tools
        $server = app(MqmsServer::class);
        $validTool = false;
        foreach ($server->tools as $toolClass) {
            if (app($toolClass)->name() === $toolName) {
                $validTool = true;
                break;
            }
        }

        if (! $validTool) {
            return response()->json([
                'status'  => 0,
                'message' => "Invalid tool '{$toolName}'. Tool not registered in MCP Server.",
            ], 404);
        }

        $config = McpToolConfig::firstOrNew(['tool_name' => $toolName]);
        $config->custom_description = $customDescription !== null ? trim((string) $customDescription) : null;
        $config->updated_by = Auth::id();
        $config->save();

        return response()->json([
            'status'  => 1,
            'message' => 'Tool description updated successfully.',
            'data'    => $config,
        ]);
    }

    /**
     * Reset a tool's description back to its hardcoded default.
     */
    public function _reset(Request $request): JsonResponse
    {
        $toolName = (string) $request->input('tool_name');

        if (! $toolName) {
            return response()->json([
                'status'  => 0,
                'message' => 'Tool name is required.',
            ], 400);
        }

        $config = McpToolConfig::where('tool_name', $toolName)->first();
        if ($config) {
            $config->custom_description = null;
            $config->updated_by = Auth::id();
            $config->save();
        }

        return response()->json([
            'status'  => 1,
            'message' => "Tool '{$toolName}' description reset to default successfully.",
        ]);
    }
}
