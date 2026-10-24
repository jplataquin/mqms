<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Protocols\ProtocolRegistry;

class ProtocolController extends Controller
{
    /**
     * Display the Protocols Dashboard view.
     */
    public function list()
    {
        return view('protocols/list');
    }

    /**
     * Display the dedicated print view for a protocol diagnostic.
     */
    public function print(Request $request)
    {
        $protocolSlug = $request->input('protocol');
        $projectId = (int) $request->input('project_id');

        if (!$protocolSlug || !$projectId) {
            abort(400, 'Protocol and Project ID are required.');
        }

        $project = \App\Models\Project::findOrFail($projectId);

        $params = [
            'project_id'        => $projectId,
            'component_id'      => $request->input('component_id') ? (int) $request->input('component_id') : null,
            'component_item_id' => $request->input('component_item_id') ? (int) $request->input('component_item_id') : null,
            'contract_item_id'  => $request->input('contract_item_id') ? (int) $request->input('contract_item_id') : null,
        ];

        $result = ProtocolRegistry::run($protocolSlug, $params);

        return view('protocols/print', [
            'protocol'  => $protocolSlug,
            'project'   => $project,
            'result'    => $result,
            'printedAt' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * API: List registered protocols.
     */
    public function _list(Request $request)
    {
        $protocols = ProtocolRegistry::all();
        $data = [];

        foreach ($protocols as $slug => $class) {
            $data[] = [
                'slug'        => $slug,
                'name'        => defined("{$class}::NAME") ? $class::NAME : $slug,
                'description' => defined("{$class}::DESCRIPTION") ? $class::DESCRIPTION : '',
            ];
        }

        return response()->json([
            'status'  => 1,
            'message' => '',
            'data'    => $data,
        ]);
    }

    /**
     * API: Run a protocol.
     */
    public function _run(Request $request)
    {
        $protocolSlug = $request->input('protocol');
        $projectId = $request->input('project_id');

        if (!$protocolSlug) {
            return response()->json([
                'status'  => 0,
                'message' => 'Protocol selection is required.',
                'data'    => null,
            ]);
        }

        if (!$projectId) {
            return response()->json([
                'status'  => 0,
                'message' => 'Project ID is required.',
                'data'    => null,
            ]);
        }

        try {
            $params = [
                'project_id'        => (int) $projectId,
                'component_id'      => $request->input('component_id') ? (int) $request->input('component_id') : null,
                'component_item_id' => $request->input('component_item_id') ? (int) $request->input('component_item_id') : null,
            ];

            $result = ProtocolRegistry::run($protocolSlug, $params);

            return response()->json([
                'status'  => 1,
                'message' => $result['summary'] ?? 'Protocol executed successfully.',
                'data'    => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 0,
                'message' => $e->getMessage(),
                'data'    => null,
            ]);
        }
    }
}
