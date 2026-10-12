<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Section;
use App\Models\ContractItem;
use App\Models\Component;
use App\Models\Accomplishment;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AccomplishmentController extends Controller
{

    public function list(){

        return view('accomplishment/list');
    }

    public function _list(Request $request){

      
        $page       = (int) ($request->input('page') ?? 1);
        $limit      = (int) ($request->input('limit') ?? 10);
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $query      = $request->input('query')          ?? '';
        $status     = $request->input('status')         ?? '';
        $result = [];

        $project = new Project();

        $project = $project->where('deleted_at',null);
        $project = $project->where('status','ACTV');

        if($query != ''){
            $project = $project->where('name','LIKE','%'.$query.'%');
        }

        if($status != ''){
            $project = $project->where('status','=',$status);
        }

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $project->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $project->orderBy($orderBy,$order)->take($limit)->get();
        }

        return response()->json([
            'status'    => 1,
            'message'   =>'',
            'data'      => $result
        ]);
    }


    public function section_list($id){

        $project = Project::findOrFail($id);

        return view('accomplishment/section_list',[
            'project' => $project
        ]);
    }

    public function print_report($id){
        $project = Project::with([
            'Sections' => function($q) { 
                $q->whereNull('deleted_at')->orderBy('name', 'asc'); 
            },
            'Sections.ContractItems' => function($q) { 
                $q->whereNull('deleted_at')
                  ->where(function($sq) {
                      $sq->where('exclude_from_monitoring', '!=', 1)
                         ->orWhereNull('exclude_from_monitoring');
                  })
                  ->orderBy('item_code', 'asc')->orderBy('description', 'asc'); 
            },
            'Sections.ContractItems.Accomplishments' => function($q) {
                $q->orderBy('entry_data', 'desc');
            }
        ])->findOrFail($id);

        return view('accomplishment/print_report', [
            'project' => $project
        ]);
    }

    public function _section_list(Request $request){


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

    public function contract_item_list($id){
        
        $section        = Section::findOrFail($id);
        $project        = $section->Project;

        return view('accomplishment/contract_item_list',[
            'section' => $section,
            'project' => $project
        ]);
    }

    public function _contract_item_list(Request $request){

        $section_id = (int) $request->input('section_id') ?? 0;
        $page       = (int) $request->input('page')     ?? 1;
        $limit      = (int) $request->input('limit')    ?? 0;
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $query      = $request->input('query')          ?? '';
        $result     = [];

        $contract_item = new ContractItem();

        if($section_id){
            $contract_item = $contract_item->where('section_id',$section_id);
        }

        if($query != ''){
            $contract_item = $contract_item->where(function($condition) use ($query){
                $condition->where('item_code','LIKE','%'.$query.'%')
                ->orWhere('description','LIKE','%'.$query.'%');
            });
        }

        //Filter out deleted records
        $contract_item = $contract_item->where('deleted_at','=',null);

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $contract_item->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $contract_item->orderBy($orderBy,$order)->get();
        }

        return response()->json([
            'status'    => 1,
            'message'   =>'',
            'data'      => $result
        ]);
    }

    public function contract_item_display($id){
        
        $contract_item = ContractItem::findOrFail($id);
        $section       = $contract_item->Section;
        $project       = $section->Project;

        $accomplishments = $contract_item->Accomplishments()->orderBy('entry_data', 'desc')->get();

        $latestActual = $contract_item->Accomplishments()
            ->where('type', 'ACTUAL')
            ->orderBy('entry_data', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        return view('accomplishment/contract_item_display',[
            'project'               => $project,
            'section'               => $section,
            'contract_item'         => $contract_item,
            'accomplishments'       => $accomplishments,
            'latestActual'          => $latestActual
        ]);
    }

    public function component_display($id){
        $component = Component::find($id);
        if ($component && $component->contract_item_id) {
            return redirect('/accomplishment/contract_item/' . $component->contract_item_id);
        }
        return redirect('/accomplishment');
    }

    public function create($contract_item_id){

        $contract_item = ContractItem::findOrFail($contract_item_id);
        $section       = $contract_item->Section;
        $project       = $section->Project;

        return view('accomplishment/create',[
            'project'               => $project,
            'section'               => $section,
            'contract_item'         => $contract_item,
        ]);
    }

    public function _create(Request $request){

        $contract_item_id = $request->input('contract_item_id');
        if(!$contract_item_id && $request->input('component_id')) {
            $comp = Component::find($request->input('component_id'));
            if($comp) {
                $contract_item_id = $comp->contract_item_id;
            }
        }

        $entry_date = $request->input('entry_date') ?? $request->input('entry_data');

        $validator = Validator::make(array_merge($request->all(), [
            'contract_item_id' => $contract_item_id,
            'entry_date'       => $entry_date
        ]), [
            'contract_item_id' => 'required|integer|exists:contract_items,id',
            'type'             => 'required|in:ACTUAL,TARGET',
            'entry_date'       => 'required|date',
            'quantity'         => 'required|numeric',
            'remarks'          => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $contract_item = ContractItem::findOrFail($contract_item_id);
        if ($request->input('quantity') > $contract_item->contract_quantity) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => [
                    'quantity' => ['The quantity must not be greater than the total quantity of the contract item.']
                ]
            ]);
        }

        $type = $request->input('type');
        $latestAccomplishment = $contract_item->Accomplishments()
            ->where('type', $type)
            ->orderBy('entry_data', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($latestAccomplishment && (float) $request->input('quantity') <= (float) $latestAccomplishment->quantity) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => [
                    'quantity' => ['The quantity must not be lower than or equal to the last previous accomplishment record quantity.']
                ]
            ]);
        }

        $user_id = Auth::user()->id;

        $accomplishment = new Accomplishment();
        $accomplishment->contract_item_id = $contract_item_id;
        $accomplishment->type             = $type;
        $accomplishment->entry_data       = $entry_date;
        $accomplishment->quantity         = $request->input('quantity');
        $accomplishment->remarks          = $request->input('remarks');
        $accomplishment->created_by       = $user_id;

        $accomplishment->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => $accomplishment
        ]);
    }

    public function _add(Request $request){

        if(!$this->hasAccess(['accomplishment:all:create'])){
            return response()->json([
                'status'    => 0,
                'message'   => 'Access Denied',
                'data'      => []
            ]);
        }

        $contract_item_id = $request->input('contract_item_id');
        if(!$contract_item_id && $request->input('component_id')) {
            $comp = Component::find($request->input('component_id'));
            if($comp) {
                $contract_item_id = $comp->contract_item_id;
            }
        }

        $entry_data = $request->input('entry_data') ?? $request->input('entry_date');

        $validator = Validator::make(array_merge($request->all(), [
            'contract_item_id' => $contract_item_id,
            'entry_data'       => $entry_data
        ]), [
            'contract_item_id' => 'required|integer|exists:contract_items,id',
            'entry_data'       => 'required|date',
            'quantity'         => 'required|numeric',
            'remarks'          => 'required|string',
            'type'             => 'nullable|in:ACTUAL,TARGET'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $contract_item = ContractItem::findOrFail($contract_item_id);
        if ($request->input('quantity') > $contract_item->contract_quantity) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => [
                    'quantity' => ['The quantity must not be greater than the total quantity of the contract item.']
                ]
            ]);
        }

        $type = $request->input('type') ?? 'ACTUAL';
        $latestAccomplishment = $contract_item->Accomplishments()
            ->where('type', $type)
            ->orderBy('entry_data', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($latestAccomplishment && (float) $request->input('quantity') <= (float) $latestAccomplishment->quantity) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => [
                    'quantity' => ['The quantity must not be lower than or equal to the last previous accomplishment record quantity.']
                ]
            ]);
        }

        $user_id = Auth::user()->id;

        $accomplishment = new Accomplishment();
        $accomplishment->contract_item_id = $contract_item_id;
        $accomplishment->type             = $type;
        $accomplishment->entry_data       = $entry_data;
        $accomplishment->quantity         = $request->input('quantity');
        $accomplishment->remarks          = $request->input('remarks');
        $accomplishment->created_by       = $user_id;

        $accomplishment->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => $accomplishment
        ]);
    }

    public function _record_list(Request $request){

        $contract_item_id = (int) ($request->input('contract_item_id') ?? $request->input('component_id') ?? 0);
        $page             = (int) ($request->input('page') ?? 1);
        $limit            = (int) ($request->input('limit') ?? 10);
        $type             = $request->input('type')           ?? 'ACTUAL';
        $orderBy          = $request->input('order_by')       ?? 'entry_data';
        $order            = $request->input('order')          ?? 'DESC';
        $result           = [];

        $query = Accomplishment::with('CreatedBy')
                    ->where('contract_item_id', $contract_item_id)
                    ->where('type', $type);

        if($limit > 0){
            $offset = ($page-1) * $limit;
            $result = $query->orderBy($orderBy, $order)->orderBy('id', 'DESC')->skip($offset)->take($limit)->get();
        }else{
            $result = $query->orderBy($orderBy, $order)->orderBy('id', 'DESC')->get();
        }

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => $result
        ]);
    }

    public function _delete(Request $request){
        $id = $request->input('id');
        $accomplishment = Accomplishment::find($id);

        if (!$accomplishment) {
            return response()->json([
                'status'  => 0,
                'message' => 'Record not found'
            ]);
        }

        $accomplishment->deleted_by = Auth::id();
        $accomplishment->save();
        $accomplishment->delete();

        return response()->json([
            'status'  => 1,
            'message' => 'Successfully deleted accomplishment.'
        ]);
    }

    public function _exclude_contract_item(Request $request){
        $contract_item_id = (int) ($request->input('contract_item_id') ?? $request->input('component_id'));
        $contract_item = ContractItem::find($contract_item_id);

        if (!$contract_item) {
            return response()->json([
                'status'  => 0,
                'message' => 'Contract Item not found'
            ]);
        }

        $contract_item->exclude_from_monitoring = 1;
        $contract_item->save();

        return response()->json([
            'status'  => 1,
            'message' => 'Contract Item successfully excluded from accomplishment monitoring.'
        ]);
    }

    public function _include_contract_item(Request $request){
        $contract_item_id = (int) ($request->input('contract_item_id') ?? $request->input('component_id'));
        $contract_item = ContractItem::find($contract_item_id);

        if (!$contract_item) {
            return response()->json([
                'status'  => 0,
                'message' => 'Contract Item not found'
            ]);
        }

        $contract_item->exclude_from_monitoring = 0;
        $contract_item->save();

        return response()->json([
            'status'  => 1,
            'message' => 'Contract Item successfully included in accomplishment monitoring.'
        ]);
    }

    public function _exclude_component(Request $request){
        return $this->_exclude_contract_item($request);
    }

    public function _include_component(Request $request){
        return $this->_include_contract_item($request);
    }

    public function display_record($id){

        $accomplishment = Accomplishment::findOrFail($id);
        $contract_item  = $accomplishment->ContractItem;
        $section        = $contract_item->Section;
        $project        = $section->Project;

        $latestActual = $contract_item->Accomplishments()
            ->where('id', '!=', $id)
            ->where('type', 'ACTUAL')
            ->orderBy('entry_data', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        return view('accomplishment/display',[
            'project'               => $project,
            'section'               => $section,
            'contract_item'         => $contract_item,
            'accomplishment'        => $accomplishment,
            'latestActual'          => $latestActual
        ]);
    }

    public function studio($id){
        $project = Project::findOrFail($id);

        return view('accomplishment/studio',[
            'project' => $project
        ]);
    }

    public function _studio_data($id){
        $project = Project::with([
            'Sections' => function($q){
                $q->whereNull('deleted_at')->orderBy('name', 'asc');
            },
            'Sections.ContractItems' => function($q){
                $q->whereNull('deleted_at')->orderBy('item_code', 'asc');
            },
            'Sections.ContractItems.Accomplishments' => function($q){
                $q->orderBy('entry_data', 'asc');
            }
        ])->findOrFail($id);

        return response()->json([
            'status'  => 1,
            'message' => '',
            'data'    => $project
        ]);
    }
}
