<?php

namespace App\Actions;

use Illuminate\Support\Facades\Password;

class PasswordResetLink
{
    public function __invoke(array $data): string
    {
        return Password::sendResetLink($data);
    }
}
