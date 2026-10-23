<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MaterialGroup;
use App\Models\MaterialItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MaterialItemController extends Controller
{
    public function create(Request $request){

        if(!$this->hasAccess('material_item:own:create')){
            return view('access_denied');
        }

        $materialGroup = new materialGroup();

        
        $back               = $request->input('b');
        $material_group_id  = (int) $request->input('material_group_id');

        $rows = $materialGroup::orderBy('name','ASC')->get();

        return view('material_item/create',[
            'materialGroups'    => $rows,
            'back'              => $back,
            'material_group_id' => $material_group_id
        ]);
    }

    public function display($id,Request $request){

        $id = (int) $id;

        $back = $request->input('b');

        $materialItem = MaterialItem::findOrFail($id);
        
        $user = auth()->user();

        if(!$this->hasAccess(['material_item:all:view'])){

            if( !$this->hasAccess(['material_item:own:view']) ){
                // return response()->json([
                //     'status'    => 0,
                //     'message'   => 'Access Denied',
                //     'data'      => []
                // ]);

                return view('access_denied');
            }

            if($materialItem->created_by != $user->id){
                // return response()->json([
                //     'status'    => 0,
                //     'message'   => 'Access Denied',
                //     'data'      => []
                // ]);

                return view('access_denied');
            }
        }

        $materialGroup = new materialGroup();

        $materialGroupRows = $materialGroup::orderBy('name','ASC')->get();

        return view('material_item/display',[
            'materialItem'      => $materialItem,
            'materialGroups'    => $materialGroupRows,
            'back'              => $back
        ]);
    }

    // public function edit($id){

    //     return view('material_item/edit');
    // }

    public function list(Request $request){

        $materialGroups = MaterialGroup::orderBy('name', 'ASC')->get();
        $selectedGroupId = $request->input('material_group_id');

        return view('material_item/list', [
            'materialGroups'  => $materialGroups,
            'selectedGroupId' => $selectedGroupId,
        ]);
    }

    public function maintenance(Request $request){
        $query             = $request->input('query') ?? '';
        $material_group_id = $request->input('material_group_id');

        $materialItemQuery = MaterialItem::with('Group')
            ->select('material_items.*')
            ->leftJoin('material_groups', 'material_items.material_group_id', '=', 'material_groups.id')
            ->orderByRaw("COALESCE(material_groups.name, 'ZZZZZZZZ') ASC")
            ->orderBy('material_items.name', 'ASC');

        if($query != ''){
            $materialItemQuery = $materialItemQuery->where(function($q) use ($query){
                $q->where('material_items.name','LIKE','%'.$query.'%')
                  ->orWhere('material_items.brand','LIKE','%'.$query.'%')
                  ->orWhere('material_items.specification_unit_packaging','LIKE','%'.$query.'%');
            });
        }

        $selectedGroup = null;
        if($material_group_id){
            $selectedGroup = MaterialGroup::find($material_group_id);
            $materialItemQuery = $materialItemQuery->where('material_items.material_group_id', $material_group_id);
        }

        $paginator = $materialItemQuery->paginate(50);
        $paginator->appends($request->all());

        $grouped = [];
        foreach ($paginator as $item) {
            $group = $item->Group;
            $groupKey = $group ? 'group_' . $group->id : 'ungrouped';
            $groupName = $group ? $group->name : 'Ungrouped';

            if (!isset($grouped[$groupKey])) {
                $grouped[$groupKey] = [
                    'group_id'   => $group ? $group->id : null,
                    'group_name' => $groupName,
                    'items'      => [],
                ];
            }
            $grouped[$groupKey]['items'][] = $item;
        }

        $allMaterialGroups = MaterialGroup::orderBy('name', 'ASC')->get();

        return view('material_item/maintenance', [
            'paginator'            => $paginator,
            'groupedMaterialItems' => $grouped,
            'materialGroup'        => $selectedGroup,
            'selectedGroup'        => $selectedGroup,
            'allMaterialGroups'    => $allMaterialGroups,
            'query'                => $query,
        ]);
    }

    public function print(Request $request){

        $orderBy           = $request->input('order_by')       ?? 'id';
        $order             = $request->input('order')          ?? 'DESC';
        $query             = $request->input('query')          ?? '';
        $material_group_id = $request->input('material_group_id');

        if(!in_array(strtolower($order), ['asc', 'desc'])){
            $order = 'DESC';
        }
        if(!in_array(strtolower($orderBy), ['id', 'name', 'brand', 'created_at'])){
            $orderBy = 'id';
        }

        $materialItem = MaterialItem::with('Group');

        if($query != ''){
            $materialItem = $materialItem->where(function($q) use ($query){
                $q->where('name','LIKE','%'.$query.'%')
                  ->orWhere('brand','LIKE','%'.$query.'%')
                  ->orWhere('specification_unit_packaging','LIKE','%'.$query.'%');
            });
        }

        $selectedGroup = null;
        if($material_group_id){
            $selectedGroup = MaterialGroup::find($material_group_id);
            $materialItem = $materialItem->where('material_group_id', $material_group_id);
        }
        $materialGroup = $selectedGroup;

        $materialItems = $materialItem->orderBy($orderBy, $order)->get();

        $grouped = [];
        foreach ($materialItems as $item) {
            $group = $item->Group;
            $groupKey = $group ? 'group_' . $group->id : 'ungrouped';
            $groupName = $group ? $group->name : 'Ungrouped';

            if (!isset($grouped[$groupKey])) {
                $grouped[$groupKey] = [
                    'group_id'   => $group ? $group->id : null,
                    'group_name' => $groupName,
                    'items'      => [],
                ];
            }
            $grouped[$groupKey]['items'][] = $item;
        }

        uasort($grouped, function($a, $b) {
            if ($a['group_name'] === 'Ungrouped') return 1;
            if ($b['group_name'] === 'Ungrouped') return -1;
            return strcasecmp($a['group_name'], $b['group_name']);
        });

        // Under each group, sort all items in alphabetical order by name
        foreach ($grouped as &$group) {
            usort($group['items'], function($a, $b) {
                return strcasecmp($a->name, $b->name);
            });
        }
        unset($group);

        return view('material_item/print', [
            'materialItems'        => $materialItems,
            'groupedMaterialItems' => $grouped,
            'materialGroup'        => $materialGroup,
            'selectedGroup'        => $selectedGroup,
            'query'                => $query,
            'order'                => $order,
            'orderBy'              => $orderBy,
            'current_datetime'     => date('Y-m-d H:i:s'),
        ]);
    }

    public function _create(Request $request){

        if(!$this->hasAccess('material_item:own:create')){
            return response()->json([
                'status' => 0,
                'message' => 'Access Denied',
                'data' => []
            ]);
        }

        $material_group_id              = $request->input('material_group_id') ?? 0;
        $name                           = $request->input('name') ?? '';
        $specification_unit_packaging   = $request->input('specification_unit_packaging') ?? '';
        $brand                          = $request->input('brand') ?? '';

        $validator = Validator::make($request->all(),[
            'material_group_id'             => ['required','integer'],
            'name'                          => [
                'required',
                'max:255',
                 Rule::unique('material_items')->where(function ($query) 
                 use($material_group_id,$name,$specification_unit_packaging,$brand){
                    return $query
                            ->where('material_group_id',$material_group_id)
                            ->where('name', $name)
                            ->where('specification_unit_packaging', $specification_unit_packaging)
                            ->where('brand', $brand);
                 })
            ],
            'specification_unit_packaging'  => ['required','max:255'],
            'brand'                         => ['max:255']
        ]);

        if ($validator->fails()) {
            
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $materialGroup = MaterialGroup::find($material_group_id);

        if (!$materialGroup) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => [
                    'material_group_id' => ['Selected material group does not exist.']
                ]
            ]);
        }

        if (!$materialGroup->hasSpecificationTemplate()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => [
                    'specification_unit_packaging' => [
                        'The selected material group does not have a specification template configured. Material items cannot be created or edited without a specification template.'
                    ]
                ]
            ]);
        }

        if (!$materialGroup->validateSpecification($specification_unit_packaging)) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => [
                    'specification_unit_packaging' => [
                        'Specification / Unit Packaging must strictly match the template: ' . $materialGroup->specification_template
                    ]
                ]
            ]);
        }

        $user_id = Auth::user()->id;


        $materialItem = new MaterialItem();

        $materialItem->material_group_id            = $material_group_id;
        $materialItem->name                         = $name;
        $materialItem->specification_unit_packaging = $specification_unit_packaging;
        $materialItem->brand                        = $brand;
        $materialItem->created_by                   = $user_id;

        $materialItem->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id'=> $materialItem->id
            ]
        ]);
    }

    public function _update(Request $request){

        $id                             = $request->input('id') ?? 0;
        $material_group_id              = $request->input('material_group_id') ?? 0;
        $name                           = $request->input('name') ?? '';
        $specification_unit_packaging   = $request->input('specification_unit_packaging') ?? '';
        $brand                          = $request->input('brand') ?? '';

        $validator = Validator::make($request->all(),[
            'id'                            => ['required','integer'],
            'material_group_id'             => ['required','integer'],
            'name'                          => [
                'required',
                'max:255',
                 Rule::unique('material_items')->where(function ($query) 
                 use($material_group_id,$name,$specification_unit_packaging,$brand,$id){
                    return $query
                            ->where('material_group_id', $material_group_id)
                            ->where('name', $name)
                            ->where('specification_unit_packaging', $specification_unit_packaging)
                            ->where('brand', $brand)
                            ->where('id','!=',$id);
                 })
            ],
            'specification_unit_packaging'  => ['required','max:255'],
            'brand'                         => ['max:255']
        ]);

        if ($validator->fails()) {
            
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $materialItem = MaterialItem::find($id);

        if(!$materialItem){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => [
                    'id' => $id
                ]
            ]);
        }

        $user = auth()->user();

        if(!$this->hasAccess(['material_item:all:update'])){

            if( !$this->hasAccess(['material_item:own:update']) ){
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Access Denied',
                    'data'      => []
                ]);

            }

            if($materialItem->created_by != $user->id){
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Access Denied',
                    'data'      => []
                ]);

            }
        }

        $materialGroup = MaterialGroup::find($material_group_id);

        if (!$materialGroup) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => [
                    'material_group_id' => ['Selected material group does not exist.']
                ]
            ]);
        }

        if (!$materialGroup->hasSpecificationTemplate()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => [
                    'specification_unit_packaging' => [
                        'The selected material group does not have a specification template configured. Material items cannot be created or edited without a specification template.'
                    ]
                ]
            ]);
        }

        if (!$materialGroup->validateSpecification($specification_unit_packaging)) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => [
                    'specification_unit_packaging' => [
                        'Specification / Unit Packaging must strictly match the template: ' . $materialGroup->specification_template
                    ]
                ]
            ]);
        }

        $materialItem->material_group_id            = $material_group_id;
        $materialItem->name                         = $name;
        $materialItem->specification_unit_packaging = $specification_unit_packaging;
        $materialItem->brand                        = $brand;
        $materialItem->updated_by                   = $user->id;

        $materialItem->save();


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

         $page              = max(1, (int) ($request->input('page') ?? 1));
         $limit             = (int) ($request->input('limit') ?? 10);
         $orderBy           = $request->input('order_by')       ?? 'id';
         $order             = $request->input('order')          ?? 'DESC';
         $query             = $request->input('query')          ?? '';
         $material_group_id = $request->input('material_group_id');

         if(!in_array(strtolower($order), ['asc', 'desc'])){
             $order = 'DESC';
         }
         if(!in_array(strtolower($orderBy), ['id', 'name', 'brand', 'created_at'])){
             $orderBy = 'id';
         }

         $result = [];
 
         $materialItem = MaterialItem::with('Group');
 
         if($query != ''){
             $materialItem = $materialItem->where(function($q) use ($query){
                 $q->where('name','LIKE','%'.$query.'%')
                   ->orWhere('brand','LIKE','%'.$query.'%')
                   ->orWhere('specification_unit_packaging','LIKE','%'.$query.'%');
             });
         }

         if($material_group_id){
            $materialItem = $materialItem->where('material_group_id', $material_group_id);
         }
 
         if($limit > 0){
             $offset = ($page - 1) * $limit;
             
             $result = $materialItem->orderBy($orderBy,$order)->skip($offset)->take($limit)->get();
             
         }else{
 
             $result = $materialItem->orderBy($orderBy,$order)->get();
         }
 
         return response()->json([
             'status' => 1,
             'message'=>'',
             'data'=> $result
         ]);
    }

    public function _delete(Request $request){
        //Todo
    }
}
