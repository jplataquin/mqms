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
use Illuminate\Support\Facades\Validator;

class PurchaseReportController extends Controller{

    public function parameters(){

        $projects           = Project::orderBy('name','ASC')->get();
        $material_groups    = MaterialGroup::orderBy('name','ASC')->get();
        $suppliers          = Supplier::orderBy('name','ASC')->get();
        $users              = User::orderBy('name','ASC')->get();

        return view('report/purchase/parameters',[
            'projects'          => $projects,
            'suppliers'         => $suppliers,
            'material_groups'   => $material_groups
        ]);
    }

    private function _generate($request){
        
        $url = htmlspecialchars( str_replace(url('').'/','',$request->fullUrl()) );
       
        $project_id             = $request->input('project_id');
        $section_id             = $request->input('section_id');
        $contract_item_id       = (int) $request->input('contract_item_id');
        $component_id           = (int) $request->input('component_id');
        $from                   = $request->input('from');
        $to                     = $request->input('to');

        $material_group_id_arr   = explode(',',$request->input('material_groups'));
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
            'from' => [
                'nullable',
                'date_format:Y-m-d'
            ],
            'to' => [
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

        //Filter From
        if($from){
            $purchase_orders = $purchase_orders->where('approved_at','>=', $from.' 00:00:00');
        }

        if($to){
            $purchase_orders = $purchase_orders->where('approved_at','<=', $from.' 59:59:59');
        }

        $purchase_orders = $purchase_orders->where('status','APRV')->get();

        //Arrange PO ids
        $po_id_arr = [];

        foreach($purchase_orders as $po){

            $supplier_data[$po->supplier_id] = Supplier::find($po->supplier_id);
            $po_id_arr[] = $po->id;
        }

        $material_item_id_arr = [];
        

        //Filter Material Group
        if($material_group_id_arr){
            $material_items = MaterialItem::whereIn('material_group_id',$material_group_id_arr)->get();

            //Arrange Material Item Ids
            foreach($material_items as $material_item){
                $material_item_id_arr[] = $material_item->id;
            }
        }


        $purchase_order_items = PurchaseOrderItem::whereIn('purchase_order_id',$po_id_arr);
        
        //Filter Material
        if($material_item_id_arr){
            $purchase_order_items = PurchaseOrderItem::whereIn('material_item_id',$material_item_id_arr);
        }

        $purchase_order_items = $purchase_order_items->orderBy('created_at','ASC')->with('MaterialCanvass')->get();

        return [
            'purchase_order_items' => $purchase_order_items
        ];
    }

    public function generate(Request $request){

        $data = $this->_generate($request);

        return view('/report/purchase/generate',$data);
    }

    public function print(Request $request){

        $data = $this->_generate($request);

        return view('/report/project/print',$data);
    }


    // public function fix_po_contract_item_id(){
        
    //     //get all po
    //     $purchase_orders = PurchaseOrder::withTrashed()->get();

    //     //loop 
    //     foreach($purchase_orders as $po){

    //         echo $po->component_id.'</br>';

    //         $component = Component::withTrashed()->where('id',$po->component_id)->first();

    //         if(!$component){
    //             echo 'Component '.$po->component_id.' not found </br>';
    //             continue;
    //         }

    //         $contract_item_id  = $component->ContractItem->id;

    //         $po->contract_item_id = $contract_item_id;
    //         $po->save();
    //     }


    //     echo '--DONE--';
    // }
}