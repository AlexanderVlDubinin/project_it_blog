<?php

namespace App\Policies;

use App\Enum\UserRole;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    /**
     * Determine whether the user can perform any actions.
     * Returns true for admin users (Admin can do anything), null for other users.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === UserRole::ADMIN) {
            return true;
        }

        return null; // Passes control to the methods below for the remaining roles
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return (bool)$user->id; // True if user exists (registered & authenticated)
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Post $post): bool
    {
        $canView = false;

        if ($post->is_published) {
            $canView = true; // published posts are visible to all
        }

        if ($user->role === UserRole::MODERATOR) {
            $canView = true; // moderators can view all posts (published or not)
        }

        if ($post->user_id === $user->id && $user->role === UserRole::AUTHOR) {
            $canView = true; // authors can view their own posts
        }

        return $canView;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // only moderators and authors can create posts
        return in_array($user->role, [UserRole::MODERATOR, UserRole::AUTHOR]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Post $post): bool
    {
        // moderators cannot update admin posts
        if ($user->role === UserRole::MODERATOR && $post->user?->role === UserRole::ADMIN) {
            return false;
        }

        // authors can update their own posts, moderators can update all posts (except admin posts)
        return $post->user_id === $user->id || $user->role === UserRole::MODERATOR;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        // moderators cannot delete admin posts
        if ($user->role === UserRole::MODERATOR && $post->user?->role === UserRole::ADMIN) {
            return false;
        }

        // authors can delete their own posts, moderators can delete all posts (except admin posts)
        return $post->user_id === $user->id || $user->role === UserRole::MODERATOR;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Post $post): bool
    {
        // moderators cannot restore admin posts
        if ($user->role === UserRole::MODERATOR && $post->user?->role === UserRole::ADMIN) {
            return false;
        }

        // moderators can restore all posts (except admin posts)
        return $user->role === UserRole::MODERATOR;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        // moderators cannot force delete admin posts
        if ($user->role === UserRole::MODERATOR && $post->user?->role === UserRole::ADMIN) {
            return false;
        }

        // moderators can force delete all posts (except admin posts)
        return $user->role === UserRole::MODERATOR;
    }
}
