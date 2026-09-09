<?php

namespace App\Policies;

use App\Enum\UserRole;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
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
        // moderators can view all users
        return $user->role === UserRole::MODERATOR;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // moderators can view all users
        return $user->role === UserRole::MODERATOR;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // moderators can create users
        return $user->role === UserRole::MODERATOR;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // moderator can not edit admin
        if ($user->role === UserRole::MODERATOR && $model->role === UserRole::ADMIN) {
            return false;
        }

        // moderators can edit all users (except admin)
        return $user->role === UserRole::MODERATOR;
    }

    public function deleteAny(User $user): bool
    {
        // only admin can delete users
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return false; // False for all (except admin) users (no deletion allowed)
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        // moderators can restore all users
        return $user->role === UserRole::MODERATOR;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false; // False for all (except admin) users (no permanent deletion allowed)
    }
}
