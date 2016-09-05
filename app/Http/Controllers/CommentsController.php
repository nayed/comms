<?php

namespace Comms\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request as Req;
use Comms\Http\Requests;

use Comms\Models\Comment;

use Comms\Http\Requests\StoreCommentRequest;

class CommentsController extends Controller
{
    public function index()
    {
        $comments = Comment::allFor(Input::get('type'), Input::get('id'));

        return Response::json($comments, 200, [], JSON_NUMERIC_CHECK);
    }

    public function store(StoreCommentRequest $request)
    {
        $model_id = Input::get('commentable_id');
        $model = Input::get('commentable_type');
        if (Comment::isCommentable($model, $model_id)) {
            $comment = Comment::create([
                'commentable_id' => $model,
                'commentable_type' => $model_id,
                'content' => Input::get('content'),
                'email' => Input::get('email'),
                'username' => Input::get('username'),
                'reply' => Input::get('reply', 0),
                'ip' => $request->ip()
            ]);
            return Response::json($comment, 200, [], JSON_NUMERIC_CHECK);
        }
        else {
            return Response::json('Yo, this content is not commentable', 422);
        }
    }

    public function destroy(Comment $comment)
    {
        if ($comment->ip == Req::ip()) {
            $comment->delete();
            return Response::json($comment, 200, [], JSON_NUMERIC_CHECK);
        }
        else {
            return Response::json('Yo this is not your comment', 403);
        }
    }
}
