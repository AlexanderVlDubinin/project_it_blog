<?php

namespace App\Http\Controllers\Auth;

use App\Actions\PasswordUpdate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordRequest;
use Illuminate\Http\RedirectResponse;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(PasswordRequest $request, PasswordUpdate $passwordUpdate): RedirectResponse
    {
        $passwordUpdate($request->user(), $request->validated());

        return back()->with('status', 'password-updated');
    }
}
