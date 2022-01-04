<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Models\Users;
use App\Models\Serviceprovider;
use Illuminate\Support\Facades\Mail;
use DB;
use File;

use Illuminate\Support\Facades\Auth;

class WelcomeController extends Controller {

   
    public function __construct() {
       
    }

    function policy(){
        return view('admin.policy');
    }
    public function installMatchupApp()
    {
        $data= \App\Models\Setting::get_config(array('android_app','iphone_app'));        
        $device=app("platform");
        if($device==1)
        {
            $app=json_decode($data['android_app'],true);            
            return redirect($app['app_url']);            
        }
        else if($device==2)
        {
            $app=json_decode($data['iphone_app'],true);           
            return redirect($app['app_url']);
        }
        else {
            return redirect('/');    
        }
    }
    public function getPlayGame(){
        $view_data = [
            'header' => [
                "title" => "How To Play",
            ],
            'body' => [
                'id' => "play",
            ],
            'footer' => [
            ],
        ];
        
        return view('site.play',$view_data);
    }
    public function getIndex(){
         $view_data = [
            'header' => [
                "title" => "How To Play",
            ],
            'body' => [
                'id' => "play",
            ],
            'footer' => [
            ],
        ];
            return view('site.index',$view_data);
    }
    public function getAbout(){
        $view_data = [
            'header' => [
                "title" => "How To Play",
            ],
            'body' => [
                'id' => "play",
            ],
            'footer' => [
            ],
        ];
        return view('site.about',$view_data);
    }
}