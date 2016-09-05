<?php

use Illuminate\Database\Seeder;

use Comms\Models\Post;

class PostsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Post::create([
            'name' => 'Yo people!'
        ]);
    }
}
