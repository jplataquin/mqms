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
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SectionController extends Controller
{
    public function create($project_id){

        $project_id = (int) $project_id;

        $project = Project::findOrFail($project_id);

        return view('section/create',[
            'project' => $project
        ]);
    }

    public function display($id){

        $id = (int) $id;

        $section        = Section::findOrFail($id);

        $project        = $section->project;

        $contract_items = $section->ContractItems()->orderBy('item_code','ASC')->get();
        
        $unit_options = Unit::toOptions();


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

    public function print($id){
        ini_set('max_execution_time', 160);
        $section = Section::findOrFail($id);
        $project = $section->Project;

        $contract_items = $section->ContractItems()->orderBy('item_code','ASC')->get();

        $unit_options     = Unit::toOptions();

        $current_datetime = Carbon::now();
        $current_user     = Auth::user();
        
        $html = view('section/print',[
            'project'           => $project,
            'section'           => $section,
            'contract_items'    => $contract_items,
            'unit_options'      => $unit_options,
            'current_user'      => $current_user,
            'current_datetime'  => $current_datetime
        ])->render();
        
        
        $html2pdf = new Html2Pdf('L','LEGAL','en', false, 'UTF-8');
           
        try {
            $html2pdf->setDefaultFont("Arial");
            $html2pdf->writeHTML($html);
            $html2pdf->output('Material Budget - '.str_pad($section->id,0,6,STR_PAD_LEFT ).'.pdf');
            $html2pdf->clean();
        
        }catch(Html2PdfException $e) {
            $html2pdf->clean();
        
            $formatter = new ExceptionFormatter($e);
            echo $html;
            echo $formatter->getHtmlMessage();        
        } 
       
    }


    public function _create(Request $request){

        //todo check role

        $name           = $request->input('name') ?? '';
        $project_id     = (int) $request->input('project_id') ?? 0;

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

        $section->project_id    = $project_id;
        $section->name          = $name;
        $section->created_by    = $user_id;

        $section->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id'=> $section->id
            ]
        ]);

    }

    public function _update(Request $request){

        //todo check role

        $id         = (int) $request->input('id') ?? 0;
        $name       = $request->input('name') ?? '';
        
        $validator = Validator::make($request->all(),[
            'id'   => [
                'required',
                'integer',               
            ],
            'name' => [
                'required',
                'max:255',
                Rule::unique('sections')->where(
                function ($query) use ($id,$name) {
                    return $query
                    ->where('name', $name)
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

        $section->name                         = $name;
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
