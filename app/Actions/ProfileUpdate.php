<?php

namespace App\Actions;

use App\Models\User;

class ProfileUpdate
{
    /**
     * Update the given user's profile.
     */
    public function __invoke(User $user, array $data): User
    {
        $user->fill($data);

        // If the user's email has changed, the email verification status should be reset.
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $user;
    }
}
