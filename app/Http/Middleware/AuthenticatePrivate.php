<?php

namespace App\Http\Middleware;

use Config;
use Closure;
use Illuminate\Support\Facades\Auth;

class AuthenticatePrivate
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
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $username = Config::get('personali.internal-api-credentials.username');
            $password = Config::get('personali.internal-api-credentials.password');
            // basic auth
            $authorization = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
            if (count($authorization) == 2) {
                $credentials = explode(":", base64_decode($authorization[1]));
                if (($credentials[0] === $username) && ($credentials[1] === $password)) {
                    return $next($request);
                }
            }
        }

        $headers = ['WWW-Authenticate' => 'Basic'];
        return response('Unauthorized.', 401, $headers);
    }
}
