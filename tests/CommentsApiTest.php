<?php

use Illuminate\Support\Facades\Artisan;

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

        $comment = factory(Comment::class, 3)->create([
            'commentable_type' => 'Post',
            'commentable_id' => 1
        ]);

        //$this->assertEquals(1, Comment::count());
        $response = $this->call('GET', '/api/comments', ['type' => 'Post', 'id' => $post->id]);

        $comments = json_decode($response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(3, Comment::count());
        $this->assertSame(0, $comments[0]->reply);
    }
}