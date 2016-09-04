<?php

namespace Comms\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Input;

use Comms\Http\Requests;

use Comms\Models\Comment;

class CommentsController extends Controller
{
    public function index()
    {
        $comments = Comment::where([
            'commentable_id' => Input::get('id'),
            'commentable_type' => Input::get('type')
        ])->get();

        return Response::json($comments, 200, [], JSON_NUMERIC_CHECK);
    }
}
