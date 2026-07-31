<?php

namespace App\Policies;

use App\Enum\UserRole;
use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === UserRole::ADMIN) {
            return true;
        }

        return null; // Передает управление методам ниже для остальных ролей
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Comment $comment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id && !$comment->is_deleted;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $user->role === UserRole::MODERATOR;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Comment $comment): bool
    {
        return $user->role === UserRole::MODERATOR;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Comment $comment): bool
    {
        return $user->role === UserRole::MODERATOR;
    }
}
