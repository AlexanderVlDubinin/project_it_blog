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

    /**
     * Automatically delete the image file when the post is deleted from the database.
     */
    protected static function booted(): void
    {
        // The event is triggered BEFORE (forceDeleting, if forceDeleted - AFTER) the hard deletion from the database is performed.
        static::forceDeleting(function (Post $post) {
            if ($post->image && Storage::disk('public')->exists($post->image)) {
                Storage::disk('public')->delete($post->image);
            }
        });
    }

    /**
     * Get the user that owns the post (author).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the comments for the post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the tags for the post.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    /**
     * Get the image URL for the post.
     * transform image to image_url, see in resources/views/posts/admin/edit.blade.php
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    /**
     * Get the total number of comments for the post.
     */
    public function getTotalCommentsCountAttribute(): int
    {
        return $this->comments()->count();
    }
}
