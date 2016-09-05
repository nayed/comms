<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Request;

use Comms\Models\Comment;
use Comms\Models\Post;

class CommentsApiTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        Artisan::call('migrate');
    }

    public function testGetComments()
    {
        $post = factory(Post::class)->create();

        $comment = factory(Comment::class)->create([
            'commentable_type' => 'Post',
            'commentable_id' => $post->id
        ]);

        $comment2 = factory(Comment::class)->create([
            'commentable_type' => 'Post',
            'commentable_id' => $post->id
        ]);

        $comment3 = factory(Comment::class)->create([
            'commentable_type' => 'Post',
            'commentable_id' => $post->id,
            'reply' => 1
        ]);

        $response = $this->call('GET', '/api/comments', ['type' => 'Post', 'id' => $post->id]);

        $comments = json_decode($response->getContent());
        //dd($comments);
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertEquals(2, count($comments));
        $this->assertSame(0, $comments[0]->reply);
        $this->assertSame($comment2->id, $comments[0]->id);
        $this->assertSame(0, count($comments[0]->replies));
        $this->assertSame($comment->id, $comments[1]->id);
    }

    public function testFieldsForJson()
    {
        $post = factory(Post::class)->create();

        $comment = factory(Comment::class)->create([
            'commentable_type' => 'Post',
            'commentable_id' => $post->id
        ]);

        $reply = factory(Comment::class)->create([
            'commentable_type' => 'Post',
            'commentable_id' => $post->id,
            'reply' => $comment->id
        ]);

        $response = $this->call('GET', '/api/comments', ['type' => 'Post', 'id' => $post->id]);

        $comments = json_decode($response->getContent());

        $this->assertObjectNotHasAttribute('email', $comments[0]);
        $this->assertObjectNotHasAttribute('ip', $comments[0]);
        $this->assertObjectHasAttribute('email_md5', $comments[0]);
        $this->assertObjectHasAttribute('ip_md5', $comments[0]);
        $this->assertSame(md5($comment->ip), $comments[0]->ip_md5);

        $this->assertObjectNotHasAttribute('email', $comments[0]->replies[0]);
        $this->assertObjectNotHasAttribute('ip', $comments[0]->replies[0]);
    }

    public function testPostComment()
    {
        $post = factory(Post::class)->create();

        $comment = factory(Comment::class)->make(['commentable_id' => $post->id, 'commentable_type' => 'Post']);

        $response = $this->call('POST', '/api/comments', $comment->getAttributes());

        $response_comment = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode(), $response->getStatusCode());
        $this->assertEquals(1, Comment::count());
        $this->assertEquals(md5(Request::ip()), $response_comment->ip_md5);
    }

    public function testPostCommentOnFakeContent()
    {
        $comment = factory(Comment::class)->make(['commentable_id' => 30, 'commentable_type' => 'Posft']);
        $response = $this->call('POST', '/api/comments', $comment->getAttributes());
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals(0, Comment::count());        
    }

    public function testPostCommentWithFakeEmail()
    {
        $post = factory(Post::class)->create();

        $comment = factory(Comment::class)->make(['commentable_id' => $post->id, 'commentable_type' => 'Post', 'email' => 'fake@']);

        $response = $this->call('POST', '/api/comments', $comment->getAttributes());

        $json = json_decode($response->getContent());

        $this->assertEquals(422, $response->getStatusCode(), $response->getStatusCode());
        $this->assertEquals(0, Comment::count());
        $this->assertObjectHasAttribute('email', $json);
    }

    public function testPostCommentWithFalseReply()
    {
        $post = factory(Post::class)->create();

        $comment = factory(Comment::class)->make(['commentable_id' => $post->id, 'commentable_type' => 'Post', 'reply' => 3]);

        $response = $this->call('POST', '/api/comments', $comment->getAttributes());

        $json = json_decode($response->getContent());

        $this->assertEquals(422, $response->getStatusCode(), $response->getStatusCode());
        $this->assertEquals(0, Comment::count());
        $this->assertObjectHasAttribute('reply', $json);
    }
}