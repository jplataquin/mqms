<?php

namespace App\Http\Controllers\ProjectStudio;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Unit;
use Illuminate\Http\Request;

class StudioController extends Controller
{
    public function index($id)
    {
        $id = (int) $id;

        $project = Project::findOrFail($id);

        $user = auth()->user();

        if (!$this->hasAccess(['project:all:view'])) {
            if (!$this->hasAccess(['project:own:view'])) {
                return view('access_denied');
            }

            if ($project->created_by != $user->id) {
                return view('access_denied');
            }
        }

        return view('project_studio/index', [
            'project'      => $project,
            'unit_options' => Unit::toOptions()
        ]);
    }
}
