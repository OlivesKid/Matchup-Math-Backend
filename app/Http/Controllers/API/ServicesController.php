<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;

class ServicesController extends Controller {

    private static $bypass_url = ['anyGetRequestToken','postSplash','getPrivacyPolicy','getTermsCondition'];
    
    public function __construct() {
        
        $this->middleware('GuestAuth', ['except' => self::$bypass_url]);
    }
    
    
    public function getIndex(){
        return 'Index';
    }
    

    public function anyGetRequestToken() {
        \Log::info('Get Request Token Request');
        $settings = app('settings');
        $res = \General::success_res();
        $res['data'] = ['request_token' => csrf_token()];
        \Log::info('Get Request Token Response : '.json_encode($res));
        return \Response::json($res, 200);
    }

    public function getPrivacyPolicy() {
        $data = ["title" => "Privacy Policy | Bustiket"];
        return \View::make("m.pp", $data);
    }

    public function getTermsCondition() {
        $data = ["title" => "Terms Condition | Bustiket"];
        return \View::make("m.tnc", $data);
    }

    public function postSplash() {
        \Log::info('Splash Screen Request : '.json_encode(\Input::all()));
        \Log::info('Splash Screen Header Request : '.json_encode(\Request::header()));
//        \Log::info(\Input::all());
        $settings = app('settings');
       
        
        if(app("platform") == "1"){
            $app =json_decode($settings['android_app'],true);
            
        }else if(app("platform") == "2"){
            $app =json_decode($settings['iphone_app'],true);
        }else{
            $app =[];
        }
        
        $data = [
            'request_token' => csrf_token(),
            'app' => $app,
            'single_player_time_master'=>$settings['time_master_single_player_time'],
            'multi_player_time_master'=>$settings['time_master_multi_player_time'],
        ];
        $res = \General::success_res();
        
        $res['data'] = $data;
        \Log::info('Splash Screen Response : '.json_encode($res));
        return \Response::json($res, 200);
    }
    
}
