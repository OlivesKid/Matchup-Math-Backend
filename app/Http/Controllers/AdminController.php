<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Models\Admin\User;
use App\Models\Serviceprovider;
use Illuminate\Support\Facades\Mail;
use DB;

class AdminController extends Controller {

    private static $bypass_url = ['getIndex', 'getLogin', 'postLogin'];
    private static $logger = '';


    public function __construct() {
        $this->middleware('AdminAuth', ['except' => self::$bypass_url]);
        self::$logger = config('constant.LOGGER');
    }

    public function getIndex() {
        if (!\Auth::guard('admin')->check()) {
//            return \Response::view('errors.401', array(), 401);
            return \Redirect::to('/');
        }

        return \Redirect::to('admin/dashboard');
    }

    public function getDashboard() {

        if (!\Auth::guard('admin')->check()) {
            return \Response::view('errors.401', array(), 401);
        }
        
        
        $users = \App\Models\Users::count();
        
        $totalMpChallenges = \App\Models\MultiPlayerChallenge::count();
        $totalMpPlayChallenges = \App\Models\MultiPlayerChallengePlayer::count();
        $totalSpChallenges = \App\Models\SinglePlayerGame::count();
       
        
        
        $view_data = [
            'header' => [
                "title" => 'Dashboard | Admin Panel',
                "js" => [],
                "css" => [],
            ],
            'body' => [
                'id' => 'dashboard',
                'lable' => 'Dashboard',
                'users'=>$users,
                'totalMpChallenge'=> $totalMpChallenges,
                'totalMpPlayChallenge'=> $totalMpPlayChallenges,
                'totalSpChallenge'=> $totalSpChallenges,
            ],
            'footer' => [
                "js" => [],
                "css" => []
            ],
        ];
       
        return view("admin.dashboard", $view_data);
    }

    
    public function getLogin($sec_token = "") {

        $s = \App\Models\Admin\Settings::get_config('login_url_token');
        if ($sec_token != $s['login_url_token']) {
            return \Response::view('errors.404', array(), 404);
        }

        if (\Auth::guard("admin")->check()) {
            return \Redirect::to("admin/dashboard");
        }
        $view_data = [
            'header' => [
                'title' => 'Admin Login',
            ],
            'body'=> [
                'logger' => 'Admin',
               
            ]
        ];
        return view('admin.login',$view_data);
    }

    public function postLogin(Request $req) {
        $view_data = [
            'header' => [
                'title' => 'Admin Login',
            ],
            'body'=> [
                'logger' => 'Admin',
                
            ]
        ];
        $validator = \Validator::make(\Input::all(), \Validation::get_rules("admin", "login"));
        if ($validator->fails()) {
            $messages = $validator->messages();
            $error = $messages->all();
            
            return view('admin.login',$view_data)->withErrors($validator);
        }
        $param = $req->input();
        $res = User::doLogin($param);
        if ($res['flag'] == 0) {
            return view('admin.login',$view_data)->withErrors('Wrong User Id or Password !!');
//            return \Redirect::to('/');
        }
        return \Redirect::to("admin/dashboard");
    }

    public function getLogout() {

        \App\Models\Admin\Token::delete_token();
        \Auth::guard('admin')->logout();
        $s = \App\Models\Admin\Settings::get_config('login_url_token');
        return redirect("admin/login/" . $s['login_url_token']);
    }

    public function getProfile($msg = "") {
        $res = User::getProfile();
        $view_data = [
            'header' => [
                "title" => 'Profile | Admin Panel ',
                "js" => [],
                "css" => [],
            ],
            'body' => [
                'name' => isset($res['name']) ? $res['name'] : "",
                'email' => isset($res['email']) ? $res['email'] : "",
                'msg' => $msg
            ],
            'footer' => [
                "js" => [],
                "css" => []
            ],
        ];
        return view("admin.profile", $view_data);
    }
    
    public function postChangeAdminPassword() {
        $param = \Input::all();
//        dd($param);
        $res = User::change_admin_password($param);
//        dd($res);
        if (isset($res['flag'])) {
            if ($res['flag'] == 0) {
                return \Redirect::to('admin/profile/' . $res['msg']);
            } else if ($res['flag'] == 1) {
//                return \Redirect::to("admin/dashboard");
                return \Redirect::to("admin/logout");
            }
        }
    }
    
    public function getUserList() {

        
        $view_data = [
            'header' => [
                'title' => 'Users List',
                'css'=>['assets/css/jquery.ui.css'],
                'js'=>[],
            ],
            'body'=> [
                'id'=>'users_list',
                'lable'=>'Users',
            ],
            'footer'=>[
                'js'=>['jquery.ui.js',"moment.min.js"],
            ],
        ];
        return view('admin.users_list',$view_data);
    }

    public function postUserFilter(){
        $param = \Input::all();
        
        $users = \App\Models\Users::filter_users($param);
//        dd($users);
        $res = \General::success_res();
        $res['blade'] = view("admin.users_filter", $users)->render();
        $res['total_record'] = $users['total_record'];
//        dd($res['blade']);
        return $res;
    }
    
    public function postUserName(){
        $param = \Input::all();
        $user = \App\Models\Users::get_user($param);
        if(is_null($user)){
            return \General::error_res('no users found');
        }
        $res = \General::success_res();
        $isMobile = isset($param['is_mobile']) ? $param['is_mobile'] : 0;
        $r = [];
        foreach($user as $a){
            $r[] = [
                'key'=>$a['id'],
                'value'=>$isMobile ? $a['username'] . ' - '.$a['email'] : $a['username'],
            ];
            
        }
        
        $res['data'] = $r;
        return \Response::json($res, 200);
    }
    
    public function getUserDetail($id = '') {
        
        if($id == ''){
            return redirect('admin/user-list');
        }
        
        $user = \App\Models\Users::where('id',$id)->first();
        
        if(!$user){
            return redirect('admin/user-list');
        }
        $user = $user->toArray();
        
        
       $total_mp= \App\Models\MultiPlayerChallengePlayer::where('user_id',$id)->count();
       $total_sp= \App\Models\SinglePlayerGame::where('player_id',$id)->count();
        
       $userSpStats=\App\Models\SinglePlayerStatics::where('user_id',$id)->first();
       if(!is_null($userSpStats)){
           $userSpStats=$userSpStats->toArray();
       }
       
       $userMpStats=\App\Models\MultiPlayerStatics::where('user_id',$id)->first();
       if(!is_null($userMpStats)){
           $userMpStats=$userMpStats->toArray();
       }
       
       

        $prev = \URL::previous();
        $prev = explode('/', $prev);
        $prev = end($prev);
        $lable = '<a href="'.\URL::to('admin/user-list').'">Users</a> / User Detail / '.$user['username'];
        $id = 'users_list';
        if($prev == 'multi-player-report'){
            $id = 'multi_player_report';
            $lable = '<a href="'.\URL::to('admin/multi-player-report').'">Multi Player Report</a> / User Detail / '.$user['username'];
        }elseif($prev == 'single-player-report'){
            $id = 'single_player_report';
            $lable = '<a href="'.\URL::to('admin/single-player-report').'">Single Player Report</a> / User Detail / '.$user['username'];
        }
        
        
        $view_data = [
            'header' => [
                'title' => 'Users Detail',
                'css'=>[],
                'js'=>[],
            ],
            'body'=> [
                'id'=>$id,
                'lable'=>$lable,
                'user'=>$user,
                'total_mp'=>$total_mp,
                'total_sp'=>$total_sp,
                'user_sp_stat'=>$userSpStats,
                'user_mp_stat'=>$userMpStats,
            ],
            'footer'=>[
                'js'=>[],
            ],
        ];
        return view('admin.users_detail',$view_data);
    }
    
    public function postChangeUserStatus(){
        $param = \Input::all();
        $user = \App\Models\Users::change_user_status($param);
        return $user;
    }
    public function postChangeUserRobotStatus(){
        $param = \Input::all();
        $user = \App\Models\Users::change_user_robot_status($param);
        return $user;
    }
    public function postEditUserDetail(){
        $param = \Input::all();
        $rules = [
            'user_id'=>'required',
            'user_name'=>'required',
            'user_name'=>'required',
            'user_email'=>'required|email:unique,'.$param['user_id'],
        ];
        
        $validator = \Validator::make(\Input::all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->messages();
            $error = $messages->all();
            $json = \General::validation_error_res();
            $json['data'] = $error;
            $json['msg'] = $error[0];
            return \Response::json($json, 200);
        }
        
        $user = \App\Models\Users::edit_user_detail($param);
        return $user;
    }
    
     public function getMultiPlayerChallengeReport(){
        $view_data = [
            'header' => [
                'title' => 'Multi Player Report',
                'css'=>['assets/css/jquery.ui.css',"assets/css/daterangepicker.css"],
                'js'=>["assets/js/moment.min.js","assets/js/daterangepicker.js"],
            ],
            'body'=> [
                'id'=>'multi_player_challenge',
                'lable'=>'Multi Player Challenge',
            ],
            'footer'=>[
                'js'=>['jquery.ui.js'],
            ],
        ];
        return view('admin.multi_player_challenege_report',$view_data);
    }
    
     public function postMultiPlayerChallengeReportFilter(){
        $param = \Input::all();
        
        $users = \App\Models\MultiPlayerChallenge::filter_challenege_report($param);
//        dd($users);
        $res = \General::success_res();
        $res['blade'] = view("admin.multi_player_challenge_report_filter", $users)->render();
        $res['total_record'] = $users['total_record'];
//        dd($res['blade']);
        return $res;
    }
    
    
    
    public function getMultiPlayerReport(){
        $view_data = [
            'header' => [
                'title' => 'Multi Player Report',
                'css'=>['assets/css/jquery.ui.css',"assets/css/daterangepicker.css"],
                'js'=>["assets/js/moment.min.js","assets/js/daterangepicker.js"],
            ],
            'body'=> [
                'id'=>'multi_player_report',
                'lable'=>'Multi Player Report',
            ],
            'footer'=>[
                'js'=>['jquery.ui.js'],
            ],
        ];
        return view('admin.multi_player_report',$view_data);
    }
    
     public function postMultiPlayerReportFilter(){
        $param = \Input::all();
        
        $users = \App\Models\MultiPlayerChallengePlayer::filter_multi_player_report($param);
//        dd($users);
        $res = \General::success_res();
        $res['blade'] = view("admin.multi_player_report_filter", $users)->render();
        $res['total_record'] = $users['total_record'];
//        dd($res['blade']);
        return $res;
    }
    
     public function postUserMultiPlayerReportFilter(){
         
       $param=\Request::all();
       if(isset($param['user_id']) && $param['user_id'] != ''){
            $param['loggerId']=$param['user_id'];
        }
        
       $data=\App\Models\MultiPlayerChallengePlayer::user_multi_player_report($param);
       if ($data['flag'] == 1) {
            $res = \General::success_res();
            unset($data['flag']);
            $res['data'] = $data;
            return view("admin.user_multi_player_report_filter",$res);
        }

        $res = \General::error_res('No Data Found.');
        unset($data['flag']);
        return \Response::json($res, 200);

    }
    
    
     public function getSinglePlayerReport(){
        $view_data = [
            'header' => [
                'title' => 'Single Player Report',
                'css'=>['assets/css/jquery.ui.css',"assets/css/daterangepicker.css"],
                'js'=>["assets/js/moment.min.js","assets/js/daterangepicker.js"],
            ],
            'body'=> [
                'id'=>'single_player_report',
                'lable'=>'Single Player Report',
            ],
            'footer'=>[
                'js'=>['jquery.ui.js'],
            ],
        ];
        return view('admin.single_player_report',$view_data);
    }
    
    public function postSinglePlayerReportFilter(){
        $param = \Input::all();
        
        $users = \App\Models\SinglePlayerGame::filter_single_player_report($param);
//        dd($users);
        $res = \General::success_res();
        $res['blade'] = view("admin.single_player_report_filter", $users)->render();
        $res['total_record'] = $users['total_record'];
//        dd($res['blade']);
        return $res;
    }
    
    
   public function postUserSinglePlayerReportFilter(){
         
       $param=\Request::all();
       if(isset($param['user_id']) && $param['user_id'] != ''){
            $param['loggerId']=$param['user_id'];
        }
        
       $data=\App\Models\SinglePlayerGame::user_single_player_report($param);
       if ($data['flag'] == 1) {
            $res = \General::success_res();
            unset($data['flag']);
            $res['data'] = $data;
            return view("admin.user_single_player_report_filter",$res);
        }

        $res = \General::error_res('No Data Found.');
        unset($data['flag']);
        return \Response::json($res, 200);

    }
    
   
    public function getSettings(){
        
        $settings = \App\Models\Setting::get()->toArray();
//        dd($settings);
        $view_data = [
            'header' => [
                'title' => 'Settings',
                'css'=>[],
                'js'=>[],
            ],
            'body'=> [
                'id'=>'settings',
                'lable'=>'Settings',
                'settings'=>$settings,
            ],
            'footer'=>[
                'js'=>[],
            ],
        ];
        return view('admin.settings',$view_data);
    }
    
    public function postSaveSettings(){
        $param = \Input::all();
//        dd($param);
        $setting_type = $param['settting_type'];
        
        if($setting_type == 'general'){
            $res = \App\Models\Admin\Settings::edit_general_settings($param);
        }else if($setting_type == 'password'){
            $res = User::change_admin_password($param);
        }else{
            $res = \General::error_res('setting type is not proper');
        }
        
        return $res;
    }
    
    
    public function getMultiPlayerStatics(){
        $view_data = [
            'header' => [
                'title' => 'Multi Player Statics',
                'css'=>['assets/css/jquery.ui.css',"assets/css/daterangepicker.css"],
                'js'=>["assets/js/moment.min.js","assets/js/daterangepicker.js"],
            ],
            'body'=> [
                'id'=>'multi_player_statics',
                'lable'=>'Multi Player Statics',
            ],
            'footer'=>[
                'js'=>['jquery.ui.js'],
            ],
        ];
        return view('admin.multi_player_statics',$view_data);
    }
    
     public function postMultiPlayerStaticsFilter(){
        $param = \Input::all();
        
        $users = \App\Models\MultiPlayerStatics::filter_multi_player_statics($param);
//        dd($users);
        $res = \General::success_res();
        $res['blade'] = view("admin.multi_player_statics_filter", $users)->render();
        $res['total_record'] = $users['total_record'];
//        dd($res['blade']);
        return $res;
    }
    
    public function getSinglePlayerStatics(){
        $view_data = [
            'header' => [
                'title' => 'Single Player Statics',
                'css'=>['assets/css/jquery.ui.css',"assets/css/daterangepicker.css"],
                'js'=>["assets/js/moment.min.js","assets/js/daterangepicker.js"],
            ],
            'body'=> [
                'id'=>'single_player_statics',
                'lable'=>'Single Player Statics',
            ],
            'footer'=>[
                'js'=>['jquery.ui.js'],
            ],
        ];
        return view('admin.single_player_statics',$view_data);
    }
    
     public function postSinglePlayerStaticsFilter(){
       
        $param = \Input::all();
        $users = \App\Models\SinglePlayerStatics::filter_single_player_statics($param);
//        dd($users);
        $res = \General::success_res();
        $res['blade'] = view("admin.single_player_statics_filter", $users)->render();
        $res['total_record'] = $users['total_record'];
//        dd($res['blade']);
        return $res;
    }
    function policy(){
        return view('admin.policy');
    }
    public function postDeleteUser(){
        $param = \Input::all();
        $validator = \Validator::make($param, \Validation::get_rules("admin", "user_id"));
        if ($validator->fails()) {
            $error = $validator->messages()->all();
            $msg = isset($error[0])?$error[0]:"Please fill in the required field.";
            $res = \General::validation_error_res($msg);
            return $res;
        }
        $param['id'] = $param['delete_id'];
        $res = \App\Models\Users::delete_user($param);
        return $res;
    }

}
