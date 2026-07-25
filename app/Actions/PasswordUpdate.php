<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PasswordUpdate
{
    public function __invoke(User $user, array $data): void
    {
        $user->update([
            'password' => Hash::make($data['password']),
        ]);
    }
}
