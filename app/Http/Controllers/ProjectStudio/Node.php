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
        $type        = $request->input('type');
        $parent_id   = (int) $request->input('id');

        if($type == 'project'){
            
            return $this->_section($parent_id);
        
        }else if($type == 'section'){

            return $this->_contract_item($parent_id);
            
        }

        return response()->json([
            'status' => 0,
            'message' => 'Unknown Type',
            'data' => [$type,$parent_id]
        ]);
    }

    private function _section($parent_id){

        $rows = Section::where('project_id',$parent_id)->get();

        return response()->json([
            'status' => 1,
            'message' => '',
            'data' => [
                'type' => 'section',
                'items' => $rows
            ]
        ]);
    }


    private function _contract_item($parent_id){

        $rows = ContractItem::where('section_id',$parent_id)->get();

        return response()->json([
            'status' => 1,
            'message' => '',
            'data' => [
                'type' => 'contract_item',
                'items' => $rows
            ]
        ]);
    }
}