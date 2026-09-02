<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class RestrictAdminAccess
{
    /**
     * Handle an incoming request.
     *
     * Restricts access to the admin panel to users with the 'manage-site' permission.
     * If the user does not have the permission, they will be redirected to the dashboard.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && Gate::denies('manage-site')) {
            return redirect('/dashboard')->with('error', 'Access denied: you do not have a role to manage the site.');
        }

        return $next($request);
    }
}
