<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PasswordUpdate
{
    /**
     * Update the given user's password with the provided new password.
     */
    public function __invoke(User $user, array $data): void
    {
        $user->update([
            'password' => Hash::make($data['password']),
        ]);
    }
}
