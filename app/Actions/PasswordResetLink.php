<?php

namespace App\Actions;

use Illuminate\Support\Facades\Password;

class PasswordResetLink
{
    /**
     * Send a password reset link to the given user.
     */
    public function __invoke(array $data): string
    {
        return Password::sendResetLink($data);
    }
}
