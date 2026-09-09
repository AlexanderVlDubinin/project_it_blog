<?php

namespace App\Policies;

use App\Enum\UserRole;
use App\Models\Comment;
use App\Models\User;

class CommentPolicy
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
    public function view(User $user, Comment $comment): bool
    {
        return (bool)$user->id; // True if user exists (registered & authenticated)
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === UserRole::MODERATOR; // False for all (except admin) users (no creation allowed)
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Comment $comment): bool
    {
        $commentOwner = $comment->user;
        if ($commentOwner->role === UserRole::ADMIN) {
            return $user->role === UserRole::ADMIN; // only admin can update admin comments
        }

        // other users can update their own non-deleted comments
        return $this->isStaff($user, $comment) || ($comment->user_id === $user->id && !$comment->is_deleted);
    }

    public function deleteAny(User $user): bool
    {
        // only admin and moderator can delete comments
        return $user->role === UserRole::MODERATOR;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comment $comment): bool
    {
        // only admin and moderator can delete comments (soft delete)
        // users can delete their own non-deleted comments
        return $this->isStaff($user, $comment) || $user->id === $comment->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Comment $comment): bool
    {
        // only admin and moderator can restore comments (soft delete)
        return $this->isStaff($user, $comment);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Comment $comment): bool
    {
        // only admin and moderator can permanently delete comments
        return $this->isStaff($user, $comment);
    }

    /**
     * Helper function to check if the user is a staff (admin or moderator) member
     */
    private function isStaff(User $user, Comment $comment): bool
    {
        $commentOwner = $comment->user;

        if ($commentOwner->role === UserRole::ADMIN) {
            return $user->role === UserRole::ADMIN;
        }

        return $user->role === UserRole::MODERATOR;
    }

    /**
     * Determine whether the user can change the comment action.
     */
    public function changeCommentAction(User $user, Comment $comment): bool
    {
        // user can interact if they are an author, moderator, or admin
        return $this->isStaff($user, $comment) || $user->id === $comment->user_id;
    }
}
