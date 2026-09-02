<?php

namespace App\Http\Controllers;

use App\Actions\ToggleUserReaction;
use App\Http\Requests\UserReactionRequest;

class ReactionController extends Controller
{
    /**
     * Toggle a user's reaction (like/dislike) on a post or comment.
     */
    public function toggle(UserReactionRequest $request, ToggleUserReaction $toggleUserReaction)
    {
        $result = $toggleUserReaction($request->validated());

        return response()->json([
            'likes_count' => $result['likes_count'], // number of likes
            'dislikes_count' => $result['dislikes_count'], // number of dislikes
            'user_reaction' => $result['user_reaction'], // Transmitting the status of 'like', 'dislike' or 'none'
        ]);
    }
}
