<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'api/user/logout'

    ];
    
     public function handle($request, \Closure $next) {
        
       // dd($this->tokensMatch($request));
        // dd($request->headers->all());
        //   \Log::info('========================================START=============================================================================================================================');
        // \Log::info('input_header:'.json_encode($request->headers->all()));
        // \Log::info('input_body : '.json_encode(\Input::all()));
        // \Log::info('input_token='.$request->input('_token'));
        // \Log::info($request->session()->token().' == '.$request->input('_token'));
        //  \Log::info("input_url: ".url()->current());
        //  \Log::info('========================================END=============================================================================================================================');
        // \Log::info($request->all());
        // \Log::info($this->tokensMatch($request));
       // dd(in_array($request->path(), $this->except));
        if ($this->isReading($request) || $this->tokensMatch($request) || in_array($request->path(), $this->except)) {
            return $this->addCookieToResponse($request, $next($request));
        }
        \Log::info(\Request::wantsJson());
        if (\Request::wantsJson()) {
            \Log::info('Request Token Invalid.');
            \Log::info($request->session()->token().' == '.$request->input('_token'));
            return \Response::json(\General::request_token_expire_res(), 401);
        }

       
        throw new \Illuminate\Session\TokenMismatchException;
    }
}
