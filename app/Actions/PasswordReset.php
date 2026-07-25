<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordReset
{
    public function __invoke(array $data): string
    {
        return Password::reset(
            $data,
            function (User $user) use ($data) {
                $user->forceFill([
                    'password' => Hash::make($data['password']),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new \Illuminate\Auth\Events\PasswordReset($user));
            }
        );
    }
}
