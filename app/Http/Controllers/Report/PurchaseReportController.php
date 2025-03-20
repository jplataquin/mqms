<?php

namespace App\Http\Controllers\Report;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Project;
use App\Models\Section;
use App\Models\Component;
use App\Models\Supplier;
use App\Models\User;
use App\Models\MaterialQuantityRequestItem;
use App\Models\MaterialQuantityRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\MaterialItem;
use App\Models\MaterialGroup;
use Illuminate\Support\Facades\DB;

class PurchaseReportController extends Controller{

    public function parameters(){

        $projects = Project::where('deleted_at',null)->get();

        $material_groups = MaterialGroup::where('deleted_at',null)->get();

        $users = User::where('deleted_at',null)->get();

        return view('report/purchase/parameters',[
            'projects'          => $projects
        ]);
    }

    private function _generate($request){
        
        $url = htmlspecialchars( str_replace(url('').'/','',$request->fullUrl()) );
       
        $project_id             = $request->input('project_id');
        $section_id             = $request->input('section_id');
        $contract_item_id       = (int) $request->input('contract_item_id');
        $component_id           = (int) $request->input('component_id');
        $as_of                  = $request->input('as_of');
        
        $material_item_id_arr   = explode(',',$request->input('material_items'));
        $supplier_id_arr        = explode(',',$request->input('suppliers'));

        $project_name       = '';
        $section_name       = '';
        $contract_item_name = '*';
        $component_name     = '*';
        $as_of_display      = '*';

        $validator = Validator::make($request->all(),[
            'project_id' =>[
                'required',
                'integer',
                'gte:1'
            ],
            'section_id' =>[
                'required',
                'integer',
                'gte:1'
            ],
            'contract_item_id' =>[
                'nullable',
                'integer',
                'gte:1'
            ],
            'component_id' =>[
                'nullable',
                'integer',
                'gte:1'
            ],
            'as_of' => [
                'nullable',
                'date_format:Y-m-d'
            ]
        ]);

        if ($validator->fails()) {
            
            return view('/report/purchase/error',[
                'message'          => '',
                'validation_error' => $validator->messages()
            ]);
        }

        //Filter Project
        $purchase_orders = PurchaseOrder::where('project_id',$project_id);

        //Filter Section
        if($section_id){
            $purchase_orders = $purchase_orders->where('section_id',$section_id);
        }

        //Filter Contract Item
        if($contract_item_id){
            $purchase_orders = $purchase_orders->where('contract_item_id',$contract_item_id);
        }

        //Filter Component
        if($component_id){
            $purchase_orders = $purchase_orders->where('component_id',$component_id);
        }

        //Filter Supplier
        if($supplier_id_arr){
            $purchase_orders = $purchase_orders->whereIn('supplier_id',$supplier_id_arr);
        }

        if($as_of){
            $purchase_orders = $purchase_orders->where('approved_at','>=', $as_of.' 23:59:59');
        }
    }

    public function generate(Request $request){

        $data = $this->_generate($request);

        return view('/report/project/generate',$data);
    }

    public function print(Request $request){

        $data = $this->_generate($request);

        return view('/report/project/print',$data);
    }


    public function fix_po_contract_item_id(){
        
        //get all po
        $purchase_orders = PurchaseOrder::all();

        //loop 
        foreach($purchase_orders as $po){

            echo $po->component_id.'</br>';
            $component = Component::find($po->component_id);

            if(!$component){
                echo 'Component '.$po->component_id.' not found </br>';
                continue;
            }

            $contract_item_id  = $component->ContractItem->id;

            $po->contract_item_id = $contract_item_id;
            $po->save();
        }


        echo '--DONE--';
    }
}