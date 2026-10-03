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

    /**
     * Renames a project studio node in place.
     */
    public function rename(Request $request)
    {
        try {
            $type = $request->input('type');
            $id = (int) $request->input('id');
            $name = trim($request->input('name') ?? '');

            if (!$name) {
                return response()->json([
                    'status'  => -2,
                    'message' => 'Name cannot be empty.'
                ], 422);
            }

            if ($type === 'project') {
                $project = Project::find($id);
                if (!$project) {
                    return response()->json(['status' => 0, 'message' => 'Project not found.'], 404);
                }

                if (!$this->hasProjectAccess($project)) {
                    return response()->json(['status' => 0, 'message' => 'Access Denied.'], 403);
                }

                $project->name = $name;
                $project->save();

                return response()->json([
                    'status'  => 1,
                    'message' => 'Project renamed successfully.',
                    'data'    => [
                        'id'   => $project->id,
                        'type' => 'project',
                        'text' => $project->name
                    ]
                ]);
            } elseif ($type === 'section') {
                $section = Section::find($id);
                if (!$section) {
                    return response()->json(['status' => 0, 'message' => 'Section not found.'], 404);
                }

                if (!$this->hasProjectAccess($section->Project)) {
                    return response()->json(['status' => 0, 'message' => 'Access Denied.'], 403);
                }

                $section->name = $name;
                $section->save();

                return response()->json([
                    'status'  => 1,
                    'message' => 'Section renamed successfully.',
                    'data'    => [
                        'id'   => $section->id,
                        'type' => 'section',
                        'text' => $section->name
                    ]
                ]);
            } elseif ($type === 'contract_item') {
                $contractItem = ContractItem::find($id);
                if (!$contractItem) {
                    return response()->json(['status' => 0, 'message' => 'Contract item not found.'], 404);
                }

                if (!$this->hasProjectAccess($contractItem->Section->Project)) {
                    return response()->json(['status' => 0, 'message' => 'Access Denied.'], 403);
                }

                // If user entered item_code in the name, strip it to extract description
                $itemCode = $contractItem->item_code;
                if ($itemCode && str_starts_with($name, $itemCode)) {
                    $desc = trim(substr($name, strlen($itemCode)));
                    $contractItem->description = !empty($desc) ? $desc : $name;
                } else {
                    $contractItem->description = $name;
                }

                $contractItem->save();

                return response()->json([
                    'status'  => 1,
                    'message' => 'Contract item renamed successfully.',
                    'data'    => [
                        'id'   => $contractItem->id,
                        'type' => 'contract_item',
                        'text' => $contractItem->name
                    ]
                ]);
            } elseif ($type === 'component') {
                $component = Component::find($id);
                if (!$component) {
                    return response()->json(['status' => 0, 'message' => 'Component not found.'], 404);
                }

                if (!$this->hasProjectAccess($component->ContractItem->Section->Project)) {
                    return response()->json(['status' => 0, 'message' => 'Access Denied.'], 403);
                }

                $component->name = $name;
                $component->save();

                return response()->json([
                    'status'  => 1,
                    'message' => 'Component renamed successfully.',
                    'data'    => [
                        'id'   => $component->id,
                        'type' => 'component',
                        'text' => $component->name
                    ]
                ]);
            } elseif ($type === 'component_item') {
                $componentItem = ComponentItem::find($id);
                if (!$componentItem) {
                    return response()->json(['status' => 0, 'message' => 'Component item not found.'], 404);
                }

                if (!$this->hasProjectAccess($componentItem->Component->ContractItem->Section->Project)) {
                    return response()->json(['status' => 0, 'message' => 'Access Denied.'], 403);
                }

                $componentItem->name = $name;
                $componentItem->save();

                return response()->json([
                    'status'  => 1,
                    'message' => 'Component item renamed successfully.',
                    'data'    => [
                        'id'   => $componentItem->id,
                        'type' => 'component_item',
                        'text' => $componentItem->name
                    ]
                ]);
            }

            return response()->json([
                'status'  => 0,
                'message' => 'Invalid node type.'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 0,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
