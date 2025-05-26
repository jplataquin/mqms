<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;

class BudgetController extends Controller
{

    public function list(){

        return view('budget/list');
    }

      public function _list(Request $request){

      
        $page       = (int) $request->input('page')     ?? 1;
        $limit      = (int) $request->input('limit')    ?? 10;
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $query      = $request->input('query')          ?? '';
        $status     = $request->input('status')         ?? '';
        $result = [];

        $project = new Project();

        $project = $project->where('deleted_at',null);

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
}