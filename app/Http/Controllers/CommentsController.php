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
        $comments = Comment::allFor(Input::get('type'), Input::get('id'));

        return Response::json($comments, 200, [], JSON_NUMERIC_CHECK);
    }

    public function store()
    {
        $comment = Comment::create([
            'commentable_id' => Input::get('commentable_id'),
            'commentable_type' => Input::get('commentable_type'),
            'content' => Input::get('content'),
            'email' => Input::get('email'),
            'username' => Input::get('username'),
            'reply' => Input::get('reply', 0),
            'ip' => \Illuminate\Support\Facades\Request::ip()
        ]);

        return Response::json($comment, 200, [], JSON_NUMERIC_CHECK);
    }
}
