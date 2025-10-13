<?php
namespace App\Http\Controllers\Super;


use App\Http\Controllers\Controller;
use App\Models\PurchaseOrderItem;

class PurchaseOrderSuperController extends Controller
{

      protected function __check_over_quantity($po){


        $mr                     = $po->MaterialQuantityRequest;
        $mr_items               = $mr->Items;
        $remaining_quantity_arr = [];

        foreach($mr_items as $mr_item){
            
            if( !isset($remaining_quantity_arr[$mr_item->component_item_id]) ){
                $remaining_quantity_arr[$mr_item->component_item_id] = [];
            }

            $total_poed = PurchaseOrderItem::where('component_item_id',$mr_item->component_item_id)
            ->where('material_quantity_request_item_id',$mr_item->id)
            ->where('material_item_id',$mr_item->material_item_id)
            ->where('status','APRV')
            ->where('purchase_order_id','!=',$po->id)
            ->sum('quantity');
            
            $remaining_quantity_arr[$mr_item->component_item_id][$mr_item->material_item_id] = $mr_item->requested_quantity - $total_poed;
        }

        $po_items = $po->Items;

        $po_item_arr = [];

        foreach($po_items as $po_item){

            if(!isset($po_item_arr[$po_item->material_item_id])){
                $po_item_arr[$po_item->material_item_id] = [];
            }

            if(!isset($remaining_quantity_arr[$po_item->component_item_id][$po_item->material_item_id])){

                $po_item_arr[$po_item->material_item_id][] = 'PO Material Item not found in Material Request';
                continue;
            }

            if($remaining_quantity_arr[$po_item->component_item_id][$po_item->material_item_id] < $po_item->quantity && $po->status == 'PEND'){
         
                $po_item_arr[$po_item->material_item_id][] = 'Available remaining Material Request quantity ('.$remaining_quantity_arr[$po_item->component_item_id][$po_item->material_item_id].') is less than the PO item quantity ('.$po_item->quantity.')';
            }
        }

       
        return $po_item_arr;

    }
}