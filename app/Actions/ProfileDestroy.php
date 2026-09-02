<?php

namespace App\Actions;

use App\Models\User;

class ProfileDestroy
{
    /**
     * Delete the given user.
     */
    public function __invoke(User $user): bool
    {
        return (bool) $user->delete();
    }
}
