<?php

namespace App\Actions;

use App\Models\Comment;
use App\Models\Post;
use App\Notifications\CommentReplied;

class CreateNewComment
{
    /**
     * Creates a new comment and sends a notification to the parent comment's user if the comment is a reply.
     */
    public function __invoke(Post $post, array $data): void
    {
        // Create the comment
        $comment = $post->comments()->create([
            'body' => $data['body'],
            'parent_id' => $data['parent_id'],
            'user_id' => auth()->id(), // nullOnDelete will work if the user is deleted
        ]);

        // Send notification to the parent comment's user if the comment is a reply
        if ($comment->parent_id) {
            $parentComment = Comment::with('user')->find($comment->parent_id);

            if ($parentComment && $parentComment->user_id && $parentComment->user_id !== auth()->id()) {

                // Sending via the standard Laravel notify() method
                $parentComment->user->notify(new CommentReplied($comment, auth()->user()));
            }
        }
    }
}
