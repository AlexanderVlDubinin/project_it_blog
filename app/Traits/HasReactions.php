<?php

namespace App\Traits;

use App\Models\Like;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasReactions
{
    public function reactions(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function likesCount(): int
    {
        return $this->reactions()->where('is_like', true)->count();
    }

    public function dislikesCount(): int
    {
        return $this->reactions()->where('is_like', false)->count();
    }

    public function userReaction(): MorphOne
    {
        return $this->morphOne(Like::class, 'likeable')
            ->where('user_id', auth()->id());
    }
}
