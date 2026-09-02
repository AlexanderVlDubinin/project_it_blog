<?php

namespace App\Traits;

use App\Models\Like;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasReactions
{
    /**
     * Get the reactions for the model.
     */
    public function reactions(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    /**
     * Get the likes count for the model.
     */
    public function likesCount(): int
    {
        return $this->reactions()->where('is_like', true)->count();
    }

    /**
     * Get the dislikes count for the model.
     */
    public function dislikesCount(): int
    {
        return $this->reactions()->where('is_like', false)->count();
    }

    /**
     * Get the user reaction (like/dislike) for the model.
     */
    public function userReaction(): MorphOne
    {
        return $this->morphOne(Like::class, 'likeable')
            ->where('user_id', auth()->id());
    }
}
