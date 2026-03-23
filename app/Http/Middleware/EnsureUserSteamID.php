<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserSteamID
{

    /**
     * Handle an incoming request.
     * 
     * This middleware checks if the user's Steam ID is present in the session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->has('userSteamID')) {
            return redirect('/');
        }

        return $next($request);
    }
}