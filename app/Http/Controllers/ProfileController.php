<?php

namespace App\Http\Controllers;

use App\Actions\ProfileDestroy;
use App\Actions\ProfileUpdate;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request, ProfileUpdate $profileUpdate): RedirectResponse
    {
        $profileUpdate($request->user(), $request->validated());

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(DeleteAccountRequest $request, ProfileDestroy $profileDestroy): RedirectResponse
    {

        $user = $request->user();

        Auth::logout();

        //$this->profileRepository->destroy($user);
        $profileDestroy($user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
