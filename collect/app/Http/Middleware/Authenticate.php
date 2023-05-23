<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Auth;
use Closure;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected $guards = [];
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('r.login');
        }
    }
    public function handle( $request, Closure $next, ...$guards)
    {
        // ・ユーザマスタ.ユーザ種別： 1
        if (Auth::user() &&  Auth::user()->user_type  == 1) {
            return $next($request);
        }
        if (Auth::user())
            Auth::logout();
        return redirect()->route("r.login");
    }
}
