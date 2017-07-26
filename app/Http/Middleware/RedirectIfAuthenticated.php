<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            $user = Auth::user();
            $usersPolicy = $user->permissions;

            if (in_array('site-map-viewer', $usersPolicy['roles'])) {
                return redirect()->intended('/insites');
            }
            else {
                return redirect()->intended('/reports');
            }
        }

        return $next($request);
    }
}
