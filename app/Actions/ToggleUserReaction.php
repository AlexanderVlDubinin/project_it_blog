<?php

namespace App\Actions;

use App\Models\Comment;
use App\Models\Post;

class ToggleUserReaction
{
    /**
     * Toggle the user's reaction on a post or comment.
     *
     * For posts, the reaction switches to whether there is a like or not.
     *
     * For comments, the reaction switches to "like" or "dislike" and back
     * + switches from "like" to "dislike" and vice versa.
     */
    public function __invoke(array $data): array
    {
        // the model to be used
        $model = $data['type'] === 'post'
            ? Post::query()->findOrFail($data['id'])
            : Comment::query()->findOrFail($data['id']);

        $userId = auth()->id();
        $isLike = (bool)$data['is_like']; // like or dislike

        // Looking for an existing reaction
        $existing = $model->reactions()->where('user_id', $userId)->first();

        // If the reaction already exists
        if ($existing) {
            if ($existing->is_like == $isLike) {
                // If clicking on the same button, deleting the reaction (cancel).
                $existing->delete();
                //return response()->json(['status' => 'removed']);
            }
            // If clicking on the opposite one, changing the like to dislike
            $existing->update(['is_like' => $isLike]);
            //return response()->json(['status' => 'changed']);
        } else { // if there was no reaction
            // If there was no reaction, create it
            $model->reactions()->create([
                'user_id' => $userId,
                'is_like' => $isLike
            ]);
        }

        // Get the current reaction
        $currentReaction = $model->reactions()->where('user_id', $userId)->first();
        $userReactionStatus = 'none';

        // If the current reaction exists
        if ($currentReaction) {
            $userReactionStatus = $currentReaction->is_like ? 'like' : 'dislike';
        }

        return [
            'likes_count' => $model->likesCount(), // number of likes
            'dislikes_count' => $model->dislikesCount(), // number of dislikes
            'user_reaction' => $userReactionStatus, // Transmitting the status of 'like', 'dislike' or 'none'
        ];
    }
}
