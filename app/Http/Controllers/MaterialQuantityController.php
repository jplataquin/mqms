<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MaterialItem;
use App\Models\MaterialQuantity;
use App\Models\MaterialQuantityRequestItem;
use App\Models\MaterialQuantityRequest;
use App\Models\ComponentItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class MaterialQuantityController extends Controller
{

    public function _create(Request $request){

        //Check role

        $component_item_id              = $request->input('component_item_id');
        $material_item_id               = $request->input('material_item_id');
        $equivalent                     = $request->input('equivalent');
        $quantity                       = $request->input('quantity');

        $validator = Validator::make($request->all(),[
            'material_item_id'               => [
                'required',
                'integer',
                Rule::unique('material_quantities')->where(
                    function ($query) use ($component_item_id,$material_item_id) {
                        return $query
                        ->where('component_item_id', $component_item_id)
                        ->where('material_item_id', $material_item_id)
                        ->where('deleted_at',null);
                })
            ],
            'component_item_id'         => ['required','integer'],
            'equivalent'                => ['required','numeric'],
            'quantity'                  => ['required','numeric']
        ]);

        if($validator->fails()){
            
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        //Get Component Item
        $component_item = ComponentItem::find($component_item_id);

        if(!$component_item){
            
            return response()->json([
                'status'    => 0,
                'message'   => 'Component Item record not found',
                'data'      => []
            ]);
        }

        //Check if total entries is not more than component item quantity
        $entries = MaterialQuantity::where('component_item_id',$component_item_id)->get();

        $grand_total = 0;

        foreach($entries as $entry){
            $grand_total = $grand_total + ($entry->quantity * $entry->equivalent);
        }

        $grand_total = $grand_total + ($quantity * $equivalent);

        if($component_item->quantity < $grand_total){
            return response()->json([
                'status'    => 0,
                'message'   => 'The Grand Total Quantity ('.$grand_total.') should not be more than Component Item Quantity ('.$component_item->quantity.')',
                'data'      => []
            ]);
        }

        //Insert to database
        $user_id = Auth::user()->id;

        $materialQuantity = new MaterialQuantity();

        $materialQuantity->component_item_id      = $component_item_id;
        $materialQuantity->material_item_id       = $material_item_id;
        $materialQuantity->quantity               = round($quantity,2);
        $materialQuantity->equivalent             = $equivalent;
       
        $materialQuantity->created_by             = $user_id;

        $materialQuantity->save();

        $component = $materialQuantity->componentItem->component;
        
        //Todo enclosed in a transaction
         if($component->status != 'PEND'){
             $component->status = 'PEND';
             $component->save();
         }

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id'=> $materialQuantity->id
            ]
        ]);
    }

    public function _list(Request $request){

        $component_item_id = (int) $request->input('component_item_id');
         
        //Check if material item exists
        $componentItem  = ComponentItem::find($component_item_id);
       
        if(!$component_item_id){

           return response()->json([
               'status'    => 0,
               'message'   => 'Component item does not exists',
               'data'      => [
                   'id' => $component_item_id
               ]
           ]);

           return false;
        }

        $page       = (int) $request->input('page')     ?? 0;
        $limit      = (int) $request->input('limit')    ?? 10;
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $result     = [];

        $materialQuantity = $componentItem->MaterialQuantities();

    
        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $materialQuantity->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $materialQuantity->orderBy($orderBy,$order)->get();
        }

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> $result
        ]);
    }

    private function check_affected_material_request($quantity,$materialQuantity){

        $material_item_id   = $materialQuantity->material_item_id;
        $component_item_id  = $materialQuantity->component_item_id;
        

        $result = DB::table('material_quantity_request_items')
        ->join('material_quantity_requests', 'material_quantity_requests.id','=','material_quantity_request_items.material_quantity_request_id')
        ->where('material_quantity_requests.status','APRV')
        ->where('material_quantity_requests.deleted_at',null)
        ->where('material_quantity_request_items.material_item_id',$material_item_id)
        ->where('material_quantity_request_items.component_item_id',$component_item_id)
        ->select(DB::raw('SUM(material_quantity_request_items.requested_quantity) AS total_approved_request, GROUP_CONCAT(material_quantity_requests.id) AS mqr_ids'))
        ->first();
        
        $over = false;
        
        //If request quantity is lower than the approved request
        if($quantity < $result->total_approved_request){
            $over = true;
        }

        $mqr_ids = explode(',',$result->mqr_ids);

        return (object) [
            'total_approved_request' => $result->total_approved_request,
            'over_budget'            => $over,
            'mqr_ids'                => $mqr_ids
        ];
    }

    public function test_mq($id){

        
        $materialQuantity = MaterialQuantity::find($id);

        if(!$materialQuantity){
            return false;
        }

        print_r($materialQuantity);

        $result = $this->check_affected_material_request(10000,$materialQuantity);

        echo '<br><br>';
        print_r($result);
    }

    public function _update(Request $request){

        $id         = (int) $request->input('id');
        $quantity   = $request->input('quantity');
        $equivalent = $request->input('equivalent');


        $validator = Validator::make($request->all(),[
            'id' => [
                'required',
                'integer',
                'gte:1'
            ],
            'equivalent' => [
                'required',
                'numeric',
                'gt:0'
            ],
            'quantity' => [
                'required',
                'numeric',
                'gt:0'
            ]
        ]);

        if($validator->fails()){
            
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $materialQuantity = MaterialQuantity::find($id);

        if(!$materialQuantity){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }

        //Get Component Item
        $component_item = $materialQuantity->ComponentItem;

        
  
        //Check if total entries is not more than component item quantity but ignore deleted and current row
        $entries = MaterialQuantity::where('component_item_id',$component_item->id)
        ->where('id','!=',$id) //Ignore current row being updated
        ->where('deleted_at',null) //Ignore deleted
        ->get();
  
        $grand_total = 0;

        foreach($entries as $entry){
            $grand_total = $grand_total + ($entry->quantity * $entry->equivalent);
        }
          
        $grand_total = $grand_total + ($quantity * $equivalent);

        if($component_item->quantity < $grand_total){
            return response()->json([
                'status'    => 0,
                'message'   => 'The Grand Total Quantity ('.$grand_total.') should not be more than Component Item Quantity ('.$component_item->quantity.')',
                'data'      => []
            ]);
        }
        //--------------------------------------------

        //Check if there are material request that has been affected by the change in quantity
        $check_affected = $this->check_affected_material_request($quantity,$materialQuantity);

        if($check_affected->over_budget){
            return response()->json([
                'status'    => 0,
                'message'   => 'There are "'.number_format($check_affected->total_approved_request,2).'" units of approved material request that already exists',
                'data'      => $check_affected->mqr_ids
            ]);
        }
        //--------------------------------------------
        
        
        $user_id = Auth::user()->id;

        //No change do nothing
        if($materialQuantity->quantity == $quantity && $materialQuantity == $equivalent){
            return response()->json([
                'status'    => 1,
                'message'   => '',
                'data'      => []
            ]);
        }

        $materialQuantity->quantity               = round($quantity,2);
        $materialQuantity->equivalent             = $equivalent;
        $materialQuantity->updated_by             = $user_id;

        $materialQuantity->save();

        $component = $materialQuantity->componentItem->component;
        
        //Todo enclosed in a transaction
         if($component->status != 'PEND'){
             $component->status = 'PEND';
             $component->save();
         }

         return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id'=> $materialQuantity->id
            ]
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

        $materialQuantity = MaterialQuantity::find($id);

        if(!$materialQuantity){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }

        if(!$materialQuantity->delete()){
            return response()->json([
                'status'    => 0,
                'message'   => 'Delete operation failed',
                'data'      => []
            ]);
        }
         
        $component = $materialQuantity->componentItem->component;
        
        if($component->status != 'PEND'){
            $component->status = 'PEND';
            $component->save();
        }

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }

   public function report($id){

        $id = (int) $id;

        $materialQuantity = MaterialQuantity::findOrFail($id);

        $material_item_id   = $materialQuantity->material_item_id;
        $component_item_id  = $materialQuantity->component_item_id;
        
        $materialItem    = MaterialItem::find($material_item_id);
        $componentItem   = $materialQuantity->ComponentItem;
        $component       = $componentItem->Component; 
        $contractItem    = $component->ContractItem; 
        $section         = $contractItem->Section;
        $project         = $section->Project;

        $mqr_approved = DB::table('material_quantity_request_items')
        ->join('material_quantity_requests', 'material_quantity_requests.id','=','material_quantity_request_items.material_quantity_request_id')
        ->where('material_quantity_requests.status','APRV')
        ->where('material_quantity_requests.deleted_at',null)
        ->where('material_quantity_request_items.material_item_id',$material_item_id)
        ->where('material_quantity_request_items.component_item_id',$component_item_id)
        ->select(DB::raw('SUM(material_quantity_request_items.requested_quantity) AS total, GROUP_CONCAT(material_quantity_requests.id ORDER BY material_quantity_requests.id ASC) AS mqr_ids'))
        ->first();

        $mqr_pending = DB::table('material_quantity_request_items')
        ->join('material_quantity_requests', 'material_quantity_requests.id','=','material_quantity_request_items.material_quantity_request_id')
        ->where('material_quantity_requests.status','PEND')
        ->where('material_quantity_requests.deleted_at',null)
        ->where('material_quantity_request_items.material_item_id',$material_item_id)
        ->where('material_quantity_request_items.component_item_id',$component_item_id)
        ->select(DB::raw('SUM(material_quantity_request_items.requested_quantity) AS total, GROUP_CONCAT(material_quantity_requests.id ORDER BY material_quantity_requests.id ASC) AS mqr_ids'))
        ->first();


        return view('material_quantity/report',[
            'mqr_approved' => (object) [
                'total_quantity' => $mqr_approved->total,
                'mqr_ids'        => explode(',',$mqr_approved->mqr_ids)
            ],
            'mqr_pending' => (object) [
                'total_quantity' => $mqr_pending->total,
                'mqr_ids'        => explode(',',$mqr_pending->mqr_ids)
            ],
            'material_quantity' => $materialQuantity,
            'material_item'     => $materialItem,
            'component_item'    => $componentItem,
            'component'         => $component,
            'contract_item'     => $contractItem,
            'section'           => $section,
            'project'           => $project
        ]);
   }
}