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
        return (bool)$user->id;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Comment $comment): bool
    {
        return (bool)$user->id;
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
        $commentOwner = $comment->user;
        if ($commentOwner->role === UserRole::ADMIN) {
            return $user->role === UserRole::ADMIN;
        }

        return $comment->user_id === $user->id && !$comment->is_deleted;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $this->isStaff($user, $comment) || $user->id === $comment->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Comment $comment): bool
    {
        return $this->isStaff($user, $comment);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Comment $comment): bool
    {
        return $this->isStaff($user, $comment);
    }

    private function isStaff(User $user, Comment $comment): bool
    {
        $commentOwner = $comment->user;

        if ($commentOwner->role === UserRole::ADMIN) {
            return $user->role === UserRole::ADMIN;
        }

        return $user->role === UserRole::MODERATOR;
    }

    public function changeCommentAction(User $user, Comment $comment): bool
    {
        // user can interact if they are an author, moderator, or admin
        return $this->isStaff($user, $comment) || $user->id === $comment->user_id;
    }
}
