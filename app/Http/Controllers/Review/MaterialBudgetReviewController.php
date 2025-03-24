<?php
namespace App\Http\Controllers\Review;

use App\Http\Controllers\Controller;
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

class MaterialBudgetReviewController extends Controller
{

    public function display($section_id){

        $section_id = (int)$section_id;

        return view('/review/material_budget/display',[
            'section_id' => $section_id
        ]);
    }

    public function overview($section_id){
        $user = auth()->user();

        $section = Section::findOrFail($section_id);
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

            $components = $contract_item->Components()->orderBy('name','ASC')->get();

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

                $component_items = $component->ComponentItems()->orderBy('name','ASC')->get();

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

        return view('/review/material_budget/overview',[
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
}