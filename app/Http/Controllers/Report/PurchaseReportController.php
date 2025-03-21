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
       
        $project_id             = (int) $request->input('project_id');
        $section_id             = (int) $request->input('section_id');
        $contract_item_id       = (int) $request->input('contract_item_id');
        $component_id           = (int) $request->input('component_id');
        $material_group_id      = (int) $request->input('material_group_id');
        $from                   = $request->input('from');
        $to                     = $request->input('to');
        $material_items         = $request->input('material_items');
        $suppliers              = $request->input('suppliers');

        //for supplier filter by id
        $supplier_id_arr        = [];

        //for material item filter by id
        $material_item_id_arr   = [];

        if($suppliers){
            $supplier_id_arr = explode(',',$suppliers);
        }

         //If material group id exists but material items list is empty
         if($material_group_id && !$material_items){
            
            $material_row = MaterialItem::where('material_group_id',$material_group_id)
            ->selectRaw('GROUP_CONCAT(id) as ids')
            ->groupBy('material_group_id')
            ->first();

            if($material_row->ids){
                $material_item_id_arr = explode(',',$material_row->ids);
            }

        }else if($material_group_id && $material_items){
            
            $material_row = MaterialItem::where('material_group_id',$material_group_id)
            ->selectRaw('GROUP_CONCAT(id) as ids')
            ->whereIn('id',explode(',',$material_items))
            ->groupBy('material_group_id')
            ->first();

     
            if($material_row->ids){
                $material_item_id_arr = explode(',',$material_row->ids);
            }
        }

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
            'material_group_id' =>[
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
        if(count($supplier_id_arr)){
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


        $po_by_supplier = [];
        $po_id_arr      = [];

        foreach($purchase_orders as $po){

            $po_id_arr[] = $po->id;

            if(!isset($po_by_supplier[$po->supplier_id])){
                $po_by_supplier[$po->supplier_id] = [];
            }

            $po_by_supplier[$po->supplier_id][] = $po->id;

        }

        $per_supplier = [];

        foreach($po_by_supplier as $sup_id => $po_ids){

        
            $purchase_order_items = PurchaseOrderItem::whereIn('purchase_order_id',$po_ids)
            ->selectRaw('SUM(quantity) as total_quantity, material_item_id, price')
            ->groupBy('material_item_id','price');
            
            //Filter material item
            if($material_item_id_arr){
                $purchase_order_items = $purchase_order_items->whereIn('material_item_id',$material_item_id_arr);
            }
            
            $purchase_order_items = $purchase_order_items->with('MaterialItem')->get();

            if($purchase_order_items){

                $per_supplier[$sup_id] = [
                    'supplier' => Supplier::find($sup_id),
                    'items'    => $purchase_order_items
                ];
            }
             
        }
        
        $per_material = [];

        if($po_id_arr){
            $per_material = PurchaseOrderItem::whereIn('purchase_order_id',$po_id_arr)
            ->selectRaw('SUM(quantity) as total_quantity, material_item_id')
            ->groupBy('material_item_id');
            
             //Filter material item
            if($material_item_id_arr){
                $per_material = $per_material->whereIn('material_item_id',$material_item_id_arr);
            }
            
            $per_material = $per_material->with('MaterialItem')->get();
        }
       

        return [
            'per_supplier' => $per_supplier,
            'per_material' => $per_material
        ];
    }

    public function generate(Request $request){

        $data = $this->_generate($request);

        if(!is_array($data)){
            return $data;
        }
        
 
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