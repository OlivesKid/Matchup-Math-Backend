<?php

namespace App\Http\Middleware;

use Closure;

class UserAuth {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
//        dd('In Middleware');
//        if(\Auth::guard('user')->check()){
//            
//        }
//        dd(\Request::header());
        
        if (\Request::wantsJson()) {
            $token = \Request::header('AuthToken');
            if ($token == "") {
                return \Response::json(\General::session_expire_res(),401);
            }
            $already_login = \App\Models\Token::is_active(\Config::get("constant.AUTH_TOKEN_STATUS"),$token);
            if (!$already_login)
                return \Response::json(\General::session_expire_res("unauthorise"),401);
        }
        else {
//            dd(0);
            if (!\Auth::guard('user')->check()) {
//                dd('in');
//                dd(\Auth::guard('user')->check());
                $validator = \Validator::make([], []);
                $validator->errors()->add('attempt', \Lang::get('error.session_expired', []));
                return redirect()->to("user")->withErrors($validator, 'login');
            }
            
            $usr = \Auth::guard('user')->user();
            if($usr){
                $already_login = \App\Models\Token::where('type',config("constant.AUTH_TOKEN_STATUS"))
                                                    ->where('status',1)
                                                    ->where('user_id',$usr->id)->first();
                if(!$already_login){
                    \Auth::guard('user')->logout();
                    $validator = \Validator::make([], []);
                    $validator->errors()->add('attempt', \Lang::get('error.session_expired', []));
                    return redirect()->to("user")->withErrors($validator, 'login');
                }
            }
//            dd('out');
        }
        return $next($request);
    }

}
