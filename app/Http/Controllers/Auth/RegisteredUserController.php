<?php

namespace App\Http\Controllers\Auth;

use App\Actions\CreateNewUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisteredUserRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(RegisteredUserRequest $request, CreateNewUser $createUser): RedirectResponse
    {
        $user = $createUser($request->validated());

        Auth::login($user);
        $request->session()->regenerate(); // Regenerate the session ID

        return redirect(route('dashboard', absolute: false));
    }
}
