<?php

namespace App\Models;

use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory;

    /**
     * Dynamic control of the comment text.
     * If the comment is deleted, we print a stub instead of the original text.
     */
    protected function body(): Attribute
    {
        return Attribute::make(
            get: function (string $value) {
                if ($this->is_deleted) {
                    return $this->deletion_reason
                        ? "The message was deleted by the moderator. Reason: " . $this->deletion_reason
                        : "The message was deleted by the moderator.";
                }
                return $value;
            }
        );
    }

    // Connections for a tree structure
    /**
     * Get a parent's comment (which has been answered).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get direct answers to this comment (the first level of nesting).
     */
    public function children(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * Get ALL the answers recursively (all levels of nesting).
     */
    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    // Basic connections
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
