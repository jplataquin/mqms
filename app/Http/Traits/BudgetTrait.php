<?php
namespace App\Http\Traits;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Section;
use App\Models\Unit;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

trait BudgetTrait{

    public function print($section_id,$contract_item_id = null,$component_id = null){
        
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
        
        $hide = [
            'contract_item' => [],
            'component'     => []
        ];

        $contract_item_budget_total_quantity    = [];
        $component_budget_total_quantity        = [];

        $contract_items = $section->ContractItems()->orderBy('item_code','ASC')->get();

       

        //Contract Items
        foreach($contract_items as $contract_item){

            $hide_contract_item = false;

            if($contrac_item_id != null && $contract_item_id != $contract_item->id){
                $hide_contract_item = true;
                $hide['contract_item'][$contract_item->id] = true;
            }else if($contract_item_id != null && $contract_item_id == $contract_item->id){
                $hide['contract_item'][$contract_item->id] = false;
            }
              
            $contract_item_budget_total_amount    = 0;
            $contract_item_ref_1_total_amount     = 0;

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

                $hide_component = false;

                if( ($component_id != null && $component->id != $component_id) || $hide_contract_item){
                    $hide_component = true;
                    $hide['component'][$component->id] = true;
                }else if($component_id != null && $component->id == $component_id && !$hide_contract_item){
                    $hide['component'][$component->id] = false;
                }


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

                    if($hide_component){
                        $hide['component_item'][$component_item->id] = true;
                    }else{
                        $hide['component_item'][$component_item->id] = false;
                    }

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

        return view('/print/budget',[
            'hide'                                      => $hide,
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