<?php

namespace App\Http\Middleware;

use Closure;

class APIUserAuth {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        
        $token = \Request::header('AuthToken');
        $res = \App\Models\Users::is_logged_in($token);
        if($res['flag'] != 1)
        {
          
             \Log::info('API Auth..........');
             \Log::info($res);
            if (\Request::wantsJson()) {
                return \Response::json($res);
            }
            else
            {
                \Auth::guard('user')->logout();
                $validator = \Validator::make([], []);
                $validator->errors()->add('attempt', \Lang::get('error.session_expired', []));
                return redirect()->to("user")->withErrors($validator, 'login');
            }
        }
 
        return $next($request);
    }

}
