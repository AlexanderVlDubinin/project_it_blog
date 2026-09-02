<?php

namespace App\Actions;

use App\Enum\UserRole;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;

class CreateNewUser
{
    /**
     * Creates a new user.
     */
    public function __invoke(array $data): User
    {
        // Create a new user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => null,
            'role' => $data['role'] ?? UserRole::USER->value,
        ]);

        event(new Registered($user)); // Dispatch the registered event

        return $user;
    }
}
