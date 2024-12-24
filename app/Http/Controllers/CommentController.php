<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{

    public function _create(Request $request){

        $comment_type = $request->input('comment_type');
        $record_type  = $request->input('record_type');
        $record_id    = (int) $request->input('record_id');
        $content      = $request->input('content');
        
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
            ],
            'content' => [
                'required',
                'max:1500'
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

        $comment->record_id     = $record_id;
        $comment->record_type   = $record_type;
        $comment->comment_type  = $comment_type;
        $comment->content       = $content;
        $comment->created_by    = $user_id;
        
        $comment->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id'=> $comment->id
            ]
        ]);
    }
}