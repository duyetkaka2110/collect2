<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\UsersController;


class admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // 管理権限マスタの権限種別キー
        \View::share('authority_type',UsersController::authority_type());
        
        // ・ユーザマスタ.ユーザ種別： 0
        if (Auth::user() &&  Auth::user()->user_type  == 0) {
            return $next($request);
        }
        if (Auth::user())
            Auth::logout();
        return redirect()->route("a.login");
    }
}
