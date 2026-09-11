<?php

namespace App\Http\Controllers\ProjectStudio;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Section;
use App\Models\ContractItem;
use App\Models\Component;
use App\Models\ComponentItem;
use Illuminate\Http\Request;

class NodeController extends Controller
{
    /**
     * Checks if the authenticated user has access to view the given project.
     */
    private function hasProjectAccess(Project $project)
    {
        $user = auth()->user();

        if ($this->hasAccess(['project:all:view'])) {
            return true;
        }

        if ($this->hasAccess(['project:own:view']) && $project->created_by == $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Fetches the root node (Project) and its direct child nodes (Sections).
     */
    public function data(Request $request)
    {
        try {
            $projectId = (int) $request->input('project_id');

            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'status'  => 0,
                    'message' => 'Project not found.'
                ], 404);
            }

            if (!$this->hasProjectAccess($project)) {
                return response()->json([
                    'status'  => 0,
                    'message' => 'Access Denied.'
                ], 403);
            }

            // Get sections belonging to the project (SoftDeletes is handled natively by Eloquent)
            $sections = $project->Sections;

            $sectionNodes = [];
            foreach ($sections as $section) {
                $sectionNodes[] = [
                    'id'      => 'section_' . $section->id,
                    'text'    => $section->name,
                    'type'    => 'section',
                    'real_id' => $section->id,
                    'children'=> true // Indicates lazy-loading of children
                ];
            }

            $rootNode = [
                'id'       => 'project_' . $project->id,
                'text'     => $project->name,
                'type'     => 'project',
                'real_id'  => $project->id,
                'state'    => [
                    'opened' => true
                ],
                'children' => $sectionNodes
            ];

            return response()->json([$rootNode]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 0,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetches children of a node lazily based on the node type and database ID.
     */
    public function children(Request $request)
    {
        try {
            $type = $request->input('type');
            $id = (int) $request->input('id');

            $nodes = [];

            if ($type === 'section') {
                $section = Section::find($id);
                if (!$section) {
                    return response()->json([]);
                }

                // Verify access through parent project
                if (!$this->hasProjectAccess($section->Project)) {
                    return response()->json([], 403);
                }

                // SoftDeletes is handled natively by Eloquent
                $contractItems = $section->ContractItems;
                foreach ($contractItems as $ci) {
                    $nodes[] = [
                        'id'      => 'contract_item_' . $ci->id,
                        'text'    => $ci->name,
                        'type'    => 'contract_item',
                        'real_id' => $ci->id,
                        'children'=> true
                    ];
                }
            } elseif ($type === 'contract_item') {
                $contractItem = ContractItem::find($id);
                if (!$contractItem) {
                    return response()->json([]);
                }

                // Verify access through parent project
                if (!$this->hasProjectAccess($contractItem->Section->Project)) {
                    return response()->json([], 403);
                }

                // SoftDeletes is handled natively by Eloquent
                $components = $contractItem->Components;
                foreach ($components as $component) {
                    $nodes[] = [
                        'id'      => 'component_' . $component->id,
                        'text'    => $component->name,
                        'type'    => 'component',
                        'real_id' => $component->id,
                        'children'=> true
                    ];
                }
            } elseif ($type === 'component') {
                $component = Component::find($id);
                if (!$component) {
                    return response()->json([]);
                }

                // Verify access through parent project
                if (!$this->hasProjectAccess($component->ContractItem->Section->Project)) {
                    return response()->json([], 403);
                }

                // SoftDeletes is handled natively by Eloquent
                $componentItems = $component->ComponentItems;
                foreach ($componentItems as $ci) {
                    $nodes[] = [
                        'id'      => 'component_item_' . $ci->id,
                        'text'    => $ci->name,
                        'type'    => 'component_item',
                        'real_id' => $ci->id,
                        'children'=> false
                    ];
                }
            }

            return response()->json($nodes);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 0,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
