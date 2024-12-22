<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ComponentController extends Controller
{

    public function _create(Request $request){

        $comment_type = $request->input('comment_type');
        $record_type  = $request->input('record_type');
        $record_id    = (int) $request->input('record_id');
        

        $validator = Validator::make($request->all(),[
            'comment_type' =>[
                'required',
                'max:4',
                'min:4'
            ],
            'record_type' =>[
                'required',
                'max:6',
                'min:6'
            ],
            'record_id' => [
                'required',
                'integer',
                'gte:1'
            ]
        ]);
         
        if ($validator->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $user_id = Auth::user()->id;

        $comment = new Comment();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id'=> $component->id
            ]
        ]);
    }
}