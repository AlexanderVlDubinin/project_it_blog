<?php

namespace App\Models;

use App\Traits\HasReactions;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, SoftDeletes, HasReactions;

    protected $casts = [
        'is_published' => 'boolean',
    ];

    protected static function booted(): void
    {
        // The event is triggered BEFORE (forceDeleting, if forceDeleted - AFTER) the hard deletion from the database is performed.
        static::forceDeleting(function (Post $post) {
            if ($post->image && Storage::disk('public')->exists($post->image)) {
                Storage::disk('public')->delete($post->image);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    // transform image to image_url, see in resources/views/posts/admin/edit.blade.php
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function getTotalCommentsCountAttribute(): int
    {
        return $this->comments()->count();
    }
}
