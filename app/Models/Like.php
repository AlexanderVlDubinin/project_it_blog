<?php

namespace App\Models;

use Database\Factories\LikeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Like extends Model
{
    /** @use HasFactory<LikeFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_like' => 'boolean',
        ];
    }

    /**
     * Get the likeable model.
     */
    public function likeable(): MorphTo
    {
        return $this->morphTo();
    }
}
