<?php
namespace App\Http\Controllers\ProjectStudio;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Project;
use App\Models\Section;
use App\Models\Component;

class Node extends Controller
{

    public function data(Request $request){

        $type       = $request->input('type');
        $id         = (int) $request->input('id');

        if($type == 'project'){

            $data = $this->_project($id);
        }

        return response()->json($data);
    }

    public function children(Request $request){
        $type       = $request->input('type');
        $parent_id  = (int) $request->input('parent_id');

        if($type == 'project'){
            
            return $this->_section($parent_id);
        }
    }

    private function _project($id){

        $project = Project::find($id);

        if(!$project){
            return [
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ];
        }

        return [
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'type' => 'project',
                'data' => [$project]
            ]
        ];
    }

}