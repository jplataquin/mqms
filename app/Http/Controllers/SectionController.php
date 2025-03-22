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

class SectionController extends Controller
{
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

    public function print($id){
        
        $user = auth()->user();

        $section = Section::findOrFail($id);
        $project = Project::findOrFail($section->project_id);


        $data = [];

        $total_amount = (object) [
            'contract_item' => [],
            'component'     => []
        ];

        $grand_total_amount = (object) [
            'contract_material'     => 0,
            'contract_opex'         => 0,
            'contract_nonmaterial'  => 0,
            
            'ref_1_material'        => 0,
            'ref_1_opex'            => 0,
            'ref_1_nonmaterial'     => 0,

            'budget_material'       => 0,
            'budget_opex'           => 0,
            'budget_nonmaterial'    => 0
        ];

        $contract_item_budget_total_quantity    = [];
        $component_budget_total_quantity        = [];

        $contract_items = $section->ContractItems()->orderBy('item_code','ASC')->get();

       

        //Contract Items
        foreach($contract_items as $contract_item){
            
            $contract_item_budget_total_amount    = 0;
            $contract_item_ref_1_total_amount       = 0;

            $contract_item_budget_total_quantity[$contract_item->id] = 0;

         
            $contract_amount = (float) $contract_item->contract_amount;
            

            if($contract_item->item_type == 'MATR'){
                
                $grand_total_amount->contract_material +=   $contract_amount;
            
            }else if($contract_item->item_type == 'NMAT'){

                $grand_total_amount->contract_nonmaterial +=  $contract_amount;

            }else if($contract_item->item_type == 'OPEX'){

                $grand_total_amount->contract_opex +=  $contract_amount;
            }
           
            
            $contract_total_quantity[$contract_item->id] = 0;

            $components = $contract_item->Components;

            $data[$contract_item->id] = [
                'contract_item' => $contract_item,
                'components'    => []
            ];

            
            
            //Components
            foreach($components as $component){

                //Total component quantity per contract item
                if($component->sum_flag && $component->unit_id == $contract_item->unit_id){
                
                    $contract_item_budget_total_quantity[$contract_item->id] += (float) $component->quantity;
                }

                $component_items = $component->ComponentItems;

                $data[$contract_item->id]['components'][$component->id] = [
                    'component'         => $component,
                    'component_items'   => []
                ];
                

                $component_item_budget_total_amount     = 0;
                $component_item_ref_1_total_amount      = 0;

                $component_budget_total_quantity[$component->id] = 0;

                //Component Items
                foreach($component_items as $component_item){

                    $data[$contract_item->id]['components'][$component->id]['component_items'][$component_item->id] = [
                        'component_item'    => $component_item,
                        'factor_text_value' => $component_item->factorTextValue($component->use_count, $component->unit_text)
                    ];
                    
                    $component_item_budget_total_amount       += (float) $component_item->amount;
                    $component_item_ref_1_total_amount        += (float) $component_item->ref_1_amount;

                    if($component_item->sum_flag && $component_item->unit_id == $component->unit_id){
                        $component_budget_total_quantity[$component->id] += $component_item->quantity;
                    }
                }

                 $total_amount->component[$component->id] = (object) [
                    'budget'    => $component_item_budget_total_amount,
                    'ref_1'     => $component_item_ref_1_total_amount
                ];

                $contract_item_budget_total_amount   += $component_item_budget_total_amount;
                $contract_item_ref_1_total_amount    += $component_item_ref_1_total_amount;
            }//end component

            $total_amount->contract_item[$contract_item->id] = (object) [
                'budget'   => $contract_item_budget_total_amount,
                'ref_1'    => $contract_item_ref_1_total_amount
            ];


            //If budget has been manually overwritten
            if($contract_item->budget_total_amount_overwrite){
                $contract_item_budget_total_amount = $contract_item->budget_quantity * $contract_item->budget_unit_price;
            }

            if($contract_item->item_type == 'MATR'){
              
                $grand_total_amount->budget_material     +=  $contract_item_budget_total_amount;
                $grand_total_amount->ref_1_material      +=  $contract_item_ref_1_total_amount;
            
            }else if($contract_item->item_type == 'OPEX'){
                
                $grand_total_amount->budget_opex        +=  $contract_item_budget_total_amount;
                $grand_total_amount->ref_1_opex         +=  $contract_item_ref_1_total_amount;

            }else if($contract_item->item_type == 'NMAT'){

                $grand_total_amount->budget_nonmaterial        +=  $contract_item_budget_total_amount;
                $grand_total_amount->ref_1_nonmaterial         +=  $contract_item_ref_1_total_amount;
            }

           
        }//end contract
        
        $data = json_decode(json_encode($data));

        $datetime_generated = Carbon::now();

        return view('/section/print',[
            'datetime_generated'                        => $datetime_generated,
            'user'                                      => $user,
            'project'                                   => $project,
            'section'                                   => $section,
            'data'                                      => $data,
            'total_amount'                              => $total_amount,
            'grand_total_amount'                        => $grand_total_amount,
            'contract_item_budget_total_quantity'       => $contract_item_budget_total_quantity,
            'component_budget_total_quantity'           => $component_budget_total_quantity
        ]);
    }

    public function __print($id){
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
                'numeric',
                'decimal:2'
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
        $section->gross_total_amount    = $gross_total_amount;
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
