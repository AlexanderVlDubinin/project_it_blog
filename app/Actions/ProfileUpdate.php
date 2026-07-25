<?php

namespace App\Actions;

use App\Models\User;

class ProfileUpdate
{
    public function __invoke(User $user, array $data): User
    {
        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $user;
    }
}
