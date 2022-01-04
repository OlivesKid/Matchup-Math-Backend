<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
          $this->app->singleton('settings', function($app)
        {
            $settings = \App\Models\Setting::where("autoload",1)->get();
            $set = [];
            foreach ($settings as $key => $val)
            {
                $set[$val['name']] = $val['val'];
            }
            return $set;
        });
        $this->app->singleton('platform', function($app)
        {
            $ua = \Request::server("HTTP_USER_AGENT");
           \Log::info($ua);

            if(stripos($ua, "android") !== FALSE)
            {
                return \Config::get("constant.ANDROID_APP_DEVICE");
            }
            else if(stripos($ua, "iphone") !== FALSE)
            {
                return \Config::get("constant.IPHONE_APP_DEVICE");
            }
            else
            {
                return \Config::get("constant.WEB_DEVICE");
            }
        });
        
        $this->app->singleton('logged_in_user', function($app)
        {
            $logged_in = \Auth::guard('user')->check();
            if(!$logged_in)
            {
                \Auth::guard('user')->logout();
                return [];
            }
            $user_data = \Auth::guard('user')->user();
            $ua = \Request::server("HTTP_USER_AGENT");
            $ip = \Request::server("REMOTE_ADDR");
            $session = \App\Models\Token::active()->where("type",  'auth')->where("ua",$ua)->where("ip",$ip)->where("user_id",$user_data['id'])->first();
            if(is_null($session))
            {
                return [];
            }
            $user_data['auth_token'] = $session['token'];
            return $user_data;
        });
        
        $this->app->singleton('device', function($app) {
            $agent = new \Jenssegers\Agent\Agent();
            $device = "desktop";
            if($agent->isMobile() || $agent->isTablet())
            {
                $device = "mobile";
            }
            return $device;
        });
    }
}
