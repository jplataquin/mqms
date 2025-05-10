<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Section;
use App\Models\Unit;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Http\Traits\BudgetTrait;

class SectionController extends Controller
{
    use BudgetTrait;

    public function create($project_id){

        $project_id = (int) $project_id;

        $project = Project::findOrFail($project_id);

        return view('section/create',[
            'project' => $project
        ]);
    }

    public function display($id,Request $request){

        $id = (int) $id;

        $section        = Section::findOrFail($id);

        $project        = $section->project;

        $contract_items = $section->ContractItems()->orderBy('item_code','ASC')->get();
        
        $unit_options = Unit::toOptions();


        if($request->header('X-STUDIO-MODE')){
            return view('project_studio/screen/section/display',[
                'section'          => $section,
                'project'          => $project,
                'contract_items'   => $contract_items,
                'unit_options'     => $unit_options
            ]);
        }

        return view('section/display',[
            'section'          => $section,
            'project'          => $project,
            'contract_items'   => $contract_items,
            'unit_options'     => $unit_options
        ]);
    }


    public function list(){

        return view('project/list');
    }

  

    public function _create(Request $request){

        //todo check role

        $name                   = $request->input('name') ?? '';
        $gross_total_amount     = $request->input('gross_total_amount') ?? 0;
        $project_id             = (int) $request->input('project_id') ?? 0;

        //TODO check if project exists;

        $validator = Validator::make($request->all(),[
            'name' => [
                'required',
                'max:255',
                Rule::unique('sections')->where(
                function ($query) use ($project_id,$name) {
                    return $query
                    ->where('project_id', $project_id)
                    ->where('name', $name);
                }),
            ],
            'gross_total_amount' =>[
                'required',
                'numeric'
            ],
            'project_id' =>[
                'required',
                'numeric',
                'gte:1'
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        

        $user_id = Auth::user()->id;

        $section = new Section();

        $section->project_id            = $project_id;
        $section->name                  = $name;
        $section->gross_total_amount    = round($gross_total_amount,2);
        $section->created_by            = $user_id;

        $section->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => $section
        ]);

    }

    public function _update(Request $request){

        //todo check role

        $id                         = (int) $request->input('id') ?? 0;
        $name                       = $request->input('name') ?? '';
        $gross_total_amount         = $request->input('gross_total_amount') ?? 0;
        
        $section = Section::find($id);
        
        if(!$section){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => [
                    'id' => $id
                ]
            ]);
        }

        $project_id = $section->project_id;

        $validator = Validator::make($request->all(),[
            'id'   => [
                'required',
                'integer',               
            ],
            'gross_total_amount'   => [
                'required',
                'numeric'         
            ],
            'name' => [
                'required',
                'max:255',
                Rule::unique('sections')->where(
                function ($query) use ($id,$name,$project_id) {
                    return $query
                    ->where('name', $name)
                    ->where('project_id',$project_id)
                    ->where('id','!=',$id);
                }),
            ]
        ]);

        if ($validator->fails()) {
            
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $user_id = Auth::user()->id;
      

    

        $section->name                        = $name;
        $section->gross_total_amount          = $gross_total_amount;
        $section->updated_by                  = $user_id;

        $section->save();


        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id' => $id
            ]
        ]);

    }

    public function _list(Request $request){

        //todo check role

        $project_id = (int) $request->input('project_id') ?? 0;
        $page       = (int) $request->input('page')     ?? 1;
        $limit      = (int) $request->input('limit')    ?? 0;
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $query      = $request->input('query')          ?? '';
        $result     = [];

        $section = new Section();

        $section = $section->where('project_id',$project_id);

        if($query != ''){
            $section = $section->where('name','LIKE','%'.$query.'%');
        }

        //Filter deleted
        $section = $section->where('deleted_at','=',null);
        
        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $section->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $section->orderBy($orderBy,$order)->get();
        }

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> $result
        ]);
    }

    public function _delete(Request $request){

         //Check role
         $id = (int) $request->input('id');


         $validator = Validator::make($request->all(),[
             'id' => [
                 'required',
                 'integer',
             ]
         ]);
 
         if($validator->fails()){
             
             return response()->json([
                 'status'    => -2,
                 'message'   => 'Failed Validation',
                 'data'      => $validator->messages()
             ]);
         }
 
         $section = Section::find($id);
 
         if(!$section){
             return response()->json([
                 'status'    => 0,
                 'message'   => 'Record not found',
                 'data'      => []
             ]);
         }
         
         if(!$section->delete()){
            return response()->json([
                'status'    => 0,
                'message'   => 'Delete operation failed',
                'data'      => $e
            ]);
         }

         return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
        
    }
}
