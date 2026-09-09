<?php

namespace App\Models;

use App\Traits\HasReactions;
use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory, HasReactions;

    protected $perPage = 5; // Redefining the standard perPage property

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'is_deleted' => 'boolean',
        ];
    }

    /**
     * Dynamic control of the comment text.
     * If the comment is soft deleted, we print a stub instead of the original text.
     */
    protected function displayBody(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->is_deleted) {
                    return $this->deletion_reason === 'Deleted by author'
                        ? "User deleted his/her comment."
                        : "The message was deleted by the moderator. Reason: " . $this->deletion_reason;
                }
                return $this->body;
            }
        );
    }

    /**
     * A recursive relationship for greedily loading the entire parent chain up.
     * Helps to avoid the N+1 problem when calling root_comment.
     */
    public function parentRecursive(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id')->with('parentRecursive');
    }

    /**
     * An accessor for quickly getting a root comment without N+1 requests.
     */
    protected function rootComment(): Attribute
    {
        return Attribute::make(
            get: function () {
                // If this is the root
                if (!$this->parent_id) {
                    return $this;
                }

                // Going up the already loaded parentRecursive link (in-memory collection)
                $current = $this;
                while ($current->relationLoaded('parentRecursive') && $current->parentRecursive) {
                    $current = $current->parentRecursive;
                }

                // If the connection was not preloaded, do lazy loading of the database once
                if (!$current->parent_id) {
                    return $current;
                }

                // Backup option: direct query to the database if the collection has not been preloaded
                return self::whereNull('parent_id')
                    ->whereIn('id', function($query) {
                        $query->select('parent_id') // Protection against deep nesting
                        ->from('comments')
                            ->where('id', $this->id);
                    })->first() ?? $this;
            }
        );
    }

    /**
     * Getting a URL link to a specific comment, taking into account the pagination of the post
     */
    public function pageUrl(): Attribute
    {
        return Attribute::get(function () {
            // 1. Finding the root comment to know its exact creation date
            $rootComment = $this->root_comment;

            // 2. Counting the position of the root comment when DESC is sorted (new ones on top)
            $position = Comment::query()
                ->where('post_id', $this->post_id)
                ->whereNull('parent_id')
                ->where(function ($query) use ($rootComment) {
                    $query->where('created_at', '>', $rootComment->created_at)
                        ->orWhere(function ($q) use ($rootComment) {
                            $q->where('created_at', '=', $rootComment->created_at)
                                ->where('id', '>=', $rootComment->id);
                        });
                })
                ->count();

            // 3. Calculating the pagination page number
            $page = (int)ceil($position / $this->perPage);
            $page = $page > 1 ? $page : null; // if it's the first page, the page parameter can be omitted

            // 4. Generating a full-fledged named router with the parameter page=X and anchor #comment-Y
            return route('posts.show', [
                    'post' => $this->post_id,
                    'page' => $page
                ]) . '#comment-' . $this->id;
        });
    }

    /**
     * Connections for a tree structure
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
        // without user and reaction counts (likes and dislikes)
        //return $this->hasMany(Comment::class, 'parent_id');

        // With user and reaction counts (likes and dislikes)
        return $this->hasMany(Comment::class, 'parent_id')
            ->with(['user', 'userReaction'])
            ->withCount([
                'reactions as likes_count' => fn($q) => $q->where('is_like', true),
                'reactions as dislikes_count' => fn($q) => $q->where('is_like', false),
            ]);
    }

    /**
     * Get ALL the answers recursively (all levels of nesting).
     */
    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    /**
     * Basic connections
     * Get the user who posted the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault([
            'name' => 'Anonymous', // This will automatically substitute "Anonymous" on the frontend of the site with user_id=NULL.
        ]);
    }

    /**
     * Get the post to which the comment belongs.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
