<?php

namespace App\Actions;

use App\Models\Post;

class CreateNewComment
{
    public function __invoke(Post $post, array $data): void
    {
        $post->comments()->create([
            'body' => $data['body'],
            'parent_id' => $data['parent_id'],
            'user_id' => auth()->id(), // nullOnDelete will work if the user is deleted
        ]);
    }
}
