<?php

namespace App\Actions;

use App\Models\User;

class ProfileDestroy
{
    public function __invoke(User $user): bool
    {
        return (bool) $user->delete();
    }
}
