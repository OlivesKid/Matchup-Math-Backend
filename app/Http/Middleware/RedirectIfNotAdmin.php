<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotAdmin {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'admin') {
        if (\Request::wantsJson()) {
            $token = \Request::header('AuthToken');
            if ($token == "") {
                return \Response::json(\General::session_expire_res(), 401);
            }
            $already_login = \App\Models\AdminToken::is_active("auth", $token);
            if (!$already_login)
                return \Response::json(\General::session_expire_res("unauthorise"), 401);
        }
        else {
            if (!Auth::guard($guard)->check()) {
                $validator = \Validator::make([], []);
                $validator->errors()->add('attempt', \Lang::get('error.session_expired', []));
                return redirect()->to("admin/login")->withErrors($validator, 'login');
            }
        }

        return $next($request);
    }

}
