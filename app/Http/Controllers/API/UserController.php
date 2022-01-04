<?php

namespace App\Models;

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use \App\Models\Users;
use App\Lib\FCM;
use DB;

class UserController extends Controller {

    private static $bypass_url = ['getIndex', 'getLogin', 'postLogin', 'getSignup', 'postSignup', 'postResendSignupOtp', 'postVerifySignupOtp', 'postForgetPassword','postResendConfirmation',];

    public function __construct() {

        $this->middleware('APIUserAuth', ['except' => self::$bypass_url]);

        $url = \Route::getCurrentRoute()->getActionName();
        $action_name = explode("@", $url)[1];

        if (in_array($action_name, self::$bypass_url)) {
            $this->middleware('GuestAuth');
        }
    }

    public function getLogout() {
        \Log::info('Logout Request');
        \App\Models\Token::inactive_token("auth");
        \Auth::guard('user')->logout();
        return redirect("user/login/" . \Config::get("constant.LOGIN_URL_TOKEN"));
    }

    public function getRedirectToLogin() {
        \App\Models\User\Token::inactive_token("auth");
        \Auth::user()->logout();
        return redirect("user/login/" . \Config::get("constant.LOGIN_URL_TOKEN"));
    }

    
    public function postLogout() {
        

        \App\Models\Token::inactive_token('auth');
        $user = app("logged_in_user");

        \App\Models\Users::where('id',$user['id'])->update(['device_token'=>null]);
        \App\Models\Token::delete_token($user['id']);
        $res = \General::success_res("logout");
        \Log::info('Logout Response : '.json_encode($res));
        return \Response::json($res,200);
    }

    public function getIndex() {
        if (!\Auth::user()->check()) {
            return \Response::view('errors.200', array(), 200);
        }

        $view_data = [
            'header' => [
                "title" => \Config::get("constant.TITLE_SEPARATOR") . \Config::get("constant.PLATFORM_NAME"),
                "js" => [],
                "css" => [],
                "screen_name" => \Lang::get("dashboard.lbl_dashboard")
            ],
            'body' => [],
            'footer' => [
                "js" => [],
                "css" => []
            ],
        ];
        return view("user.index", $view_data);
    }

    public function getLogin($sec_token = "") {
        if ($sec_token != \Config::get("constant.LOGIN_URL_TOKEN")) {
            return \Response::view('errors.404', array(), 404);
        }
        if (\Auth::user()->check()) {
            return \Redirect::to("user");
        }

        $view_data = [
            'header' => [
                "title" => \Lang::get("login.login_title") . \Config::get("constant.TITLE_SEPARATOR") . \Config::get("constant.PLATFORM_NAME"),
                "js" => [],
                "css" => [],
                "screen_name" => \Lang::get("login.lbl_login")
            ],
            'body' => [],
            'footer' => [
                "js" => [],
                "css" => []
            ],
        ];
        return view("user.login", $view_data);
    }

    public function postLogin() {
        
        \Log::info('Login Request :' . json_encode(\Input::all()));
        
        $param = \Input::all();
        
        $setting=app('settings');
       
        
      
        $validator = \Validator::make($param, \Validation::get_rules("user", "APIlogin"));
        if ($validator->fails()) {
            $messages = $validator->messages();
            $error = $messages->all();
            $json = \General::validation_error_res("Validation Error occur");
            $json['data'] = $error;
            \Log::info('Login Response :' . json_encode($json));
            return \Response::json($json, 200);
        }

//          $param['device_token'] = isset($param['device_token'])?$param['device_token']:'dummy_device_token';
        
        
        \Log::info('Validation Successful.');
        $res = \App\Models\Users::do_login($param);
       
        if($res['flag']==0){
            \Log::info('Login Response :' . json_encode($res));
            return \Response::json($res, 200);
        }
       
       
        \Log::info('Login Response :' . json_encode($res));
        return \Response::json($res, 200);
    }

    public function postUpdateProfile() {
        $user = app("logged_in_user");

        \Log::info('Update Profile Request : ' . json_encode(\Input::all()));
        $validator = \Validator::make(\Input::all(), \Validation::get_rules("user", "update_profile"));
        if ($validator->fails()) {
            $messages = $validator->messages();
            $error = $messages->all();
            $json = \General::validation_error_res();
            $json['data'] = $error;
            $json['msg'] = $error[0];
            \Log::info('Update Profile Response : ' . json_encode($json));
            return \Response::json($json, 200);
        }
        $param = \Input::all();
        
        $param['user_id'] = $user['id'];
        $res = \App\Models\Users::update_profile($param);

        \Log::info('Update Profile Response : ' . json_encode($res));
        return \Response::json($res, 200);
    }


    public function postSignup() {
//        dd(\Input::all());

        \Log::info('User SignUp Request : ' . json_encode(\Input::all()));
        
        $settings=app('settings');

        
        $param = \Input::all();
        

         $rules=[
                'username'      => 'required|min:2|max:50',
                'email'     => 'required|email|unique:users,email|min:2|max:50',
                'dob'     => 'required|digits:4',
                'password'  => 'required|min:6|max:25',
//                'con_password' => 'required|min:6|max:25',
                'device_token' => 'required',
        ];


        $custom_msg = [
            'name.required'         => 'Name Required',
            'email.required'        => 'Email Address Required',
            'email.unique'        => 'This email has already been taken.',
            'dob.required'        => 'Date of Year  Required',
            'dob.digits'        => 'Date of Year Must 4 valid four digits',
            'email.email'           => 'Invalid Email Address',
            'password.required'     => 'Password Required',
            'device_token.required'     => 'Device Token Required',
//            'con_password.required'   => 'Confirm Password Required.',
        ];
       
    

//        if ($param['password'] != $param['con_password']) {
//
//            $res=\General::error_res("Password and Confirm password do not match.");
//            \Log::info('User SignUp Response : ' . json_encode($res));
//            return \Response::json($res, 200);
//            
//        }

        $validator = \Validator::make(\Input::all(), $rules, $custom_msg);
        if ($validator->fails()) {
            $messages = $validator->messages();
            $error = $messages->all();
            $json = \General::validation_error_res();
            $json['data'] = $error;
            $json['msg'] = 'Validation Error Occur';
            \Log::info('User SignUp Response : ' . json_encode($json));
            return \Response::json($json, 200);
        }

        $age=\General::count_age($param['dob']);
        if($age<=$settings['signup_age_limit'])
        {
             $json = \General::error_res('Age must greater than '.$settings['signup_age_limit']. ', To Register this game');
             return \Response::json($json, 200);
        }
        
        
        $res = \App\Models\Users::signup(\Input::all());
        \Log::info('User SignUp Response : ' . json_encode($res));
        return \Response::json($res, 200);
    }

    public function postForgetPassword() {
        
        \Log::info('Forget Password Request : ' . json_encode(\Input::all()));
        $validator = \Validator::make(\Input::all(), \Validation::get_rules("user", "forget_pass"));
        if ($validator->fails()) {
            $messages = $validator->messages();
            $error = $messages->all();
            $json = \General::validation_error_res();
            $json['data'] = $error;
            $json['msg'] = $error[0];
            \Log::info('Forget Password Response : ' . json_encode($json));
            return \Response::json($json, 200);
        }

        $res = \App\Models\Users::forget_password(\Input::all());
        \Log::info('Forget Password Response : ' . json_encode($res));
        return \Response::json($res, 200);
    }

    public function postResendConfirmation() {
        \Log::info('Resend Verification mail Request : ' . json_encode(\Input::all()));
        $validator = \Validator::make(\Input::all(), \Validation::get_rules("user", "forget_pass"));
        if ($validator->fails()) {
            $messages = $validator->messages();
            $error = $messages->all();
            $json = \General::validation_error_res();
            $json['data'] = $error;
            $json['msg'] = $error[0];
            \Log::info('Resend Verification mail Response : ' . json_encode($json));
            return \Response::json($json, 200);
        }
        $param = \Input::all();
        $res = \App\Models\Users::resend_confirmation($param);
//        dd($param,$res);
        \Log::info('Resend Verification mail Response : ' . json_encode($res));
        return \Response::json($res, 200);
    }

    public function postChangePassword() {
        \Log::info('Change Password Request : ' . json_encode(\Input::all()));
        $user = app("logged_in_user");
      
        $validator = \Validator::make(\Input::all(), \Validation::get_rules("user", "change_password"));
        if ($validator->fails()) {
            $error = $validator->messages()->all();
            $json = \General::validation_error_res("Necessary Field are required");
            $json['data'] = $error;
    
            \Log::info('Change Password Response : ' . json_encode($json));
            return \Response::json($json, 200);
        }

        $param = \Input::all();
        $param['user_id']=$user['id'];
        $res = \App\Models\Users::change_password($param);
        \Log::info('Change Password Response : ' . json_encode($res));
        return \Response::json($res, 200);
    }

    public function postUpdateEmail() {
        $user = app("logged_in_user");
        \Log::info('Update Email Request : ' . json_encode(\Input::all()));
        $validator = \Validator::make(\Input::all(), \Validation::get_rules("user", "update_email"));
        
        if ($validator->fails()) {
            $messages = $validator->messages();
            $error = $messages->all();
            $json = \General::validation_error_res();
            $json['data'] = $error;
            $json['msg'] = "Please Fill Required filled";
            \Log::info('Update Email Response : ' . json_encode($json));
            return \Response::json($json, 200);
        }
        
        $param = \Input::all();
        $param['user_id'] = $user['id'];
        $res = \App\Models\Users::update_email($param);

        \Log::info('Update Profile Response : ' . json_encode($res));
        return \Response::json($res, 200);
    }
    
    public function postLiveUserList(){
        
        $user = app("logged_in_user");
        \Log::info('Live User List Request : ' . json_encode(\Input::all()));
//        $validator = \Validator::make(\Input::all(), \Validation::get_rules("user", "update_email"));
//        
//        if ($validator->fails()) {
//            $messages = $validator->messages();
//            $error = $messages->all();
//            $json = \General::validation_error_res();
//            $json['data'] = $error;
//            $json['msg'] = $error[0];
//            \Log::info('Live User List Response : ' . json_encode($json));
//            return \Response::json($json, 200);
//        }
        
        $param = \Input::all();
        $param['user_id'] = $user['id'];
        $res = \App\Models\Users::live_user_list($param);

        \Log::info('Live User List Response : ' . json_encode($res));
        return \Response::json($res, 200);
        
    }
    
    public function postUpdateFriendList(){
        
        $user = app("logged_in_user");
        \Log::info('Update Friend List Request : ' . json_encode(\Input::all()));
        
        $validator = \Validator::make(\Input::all(), \Validation::get_rules("friends", "emails_list"));
        
        if ($validator->fails()) {
            $messages = $validator->messages();
            $error = $messages->all();
            $json = \General::validation_error_res();
            $json['data'] = $error;
            $json['msg'] = $error[0];
            \Log::info('Update Friends List Response : ' . json_encode($json));
            return \Response::json($json, 200);
        }
        
        $param = \Input::all();
        $param['user_id'] = $user['id'];
        $res = \App\Models\FriendsList::update_friend_list($param);

        \Log::info('Update Friends List Response : ' . json_encode($res));
        return \Response::json($res, 200);
        
    }
    
    
    public function postMyFriendList(){
        
        $user = app("logged_in_user");
        \Log::info('My Friend List Request : ' . json_encode(\Input::all()));
        

        $param=\Input::all();

        if(!isset($param['in_system'])){
            $res=\General::error_res('in_system parameter is required');
            return \Response::json($res, 200);
        }

       $validator = \Validator::make(\Input::all(), \Validation::get_rules("pagination", "api_pagination"));
       
       if ($validator->fails()) {
           $error = $validator->messages()->all();
           $json = \General::validation_error_res("Please Fill Required field");
           $json['data'] = $error;
           \Log::info('My Friends List Response : ' . json_encode($json));
           return \Response::json($json, 200);
        }
        
        
        $param['user_id'] = $user['id'];
        $friendRes = \App\Models\FriendsList::my_friend_list($param);

        if($friendRes['flag']!=1){
            $res=\General::success_res('Opps ! Something might wrong');
            return \Response::json($json,200);
        }

        unset($friendRes['flag']);    

        $res=\General::success_res('Friend list');
        $res['data']=$friendRes;

        \Log::info('My Friends List Response : ' . json_encode($res));
        return \Response::json($res, 200);
    }
    
    public function postInviteFriendMail(){
        
        $user = app("logged_in_user");
        $param = \Input::all();
        $param['user_id'] = $user['id'];
        \Log::info('Invite Friend Mail Request : ' . json_encode(\Input::all()));
        
        $validator = \Validator::make(\Input::all(), \Validation::get_rules("user", "invitation"));
        
        if($validator->fails()) {
            $messages = $validator->messages();
            $error = $messages->all();
            $json = \General::validation_error_res();
            $json['data'] = $error;
            $json['msg'] = $error[0];
            \Log::info('Invite Friend Mail Response : ' . json_encode($json));
            return \Response::json($json, 200);
        }
        

        $userm['mail_subject']='MATcHUP Invitation Request';
        $userm['from']=config('constant.SUPPORT_MAIL');
        $userm['name']=config('constant.PLATFORM_NAME');
       

//        dd($param);
        $emails=json_decode($param['emails']);
        if(count($emails)<1){
            $res = \General::error_res('Atleast One Email Address Required');
            return \Response::json($res, 200);
        }
        
        $userm['email']=$emails;
        
        $user['title']='MATcHUP Invitation Request';
        $user['msg']=$param['msg'];
        $user['challenger_name']=$user['username'];
        
        $app=app('platform');
        if($app==1){
            $user['store']=config('constant.PLAY_STORE');
        }elseif($app==2){
            $user['store']=config('constant.APP_STORE');
        }else{
            $user['store']='';
        }
        
        
        \Mail::send('emails.user.invite_mail',$user,function ($message) use ($userm) {
            $message->from($userm['from'],$userm['name'])->to($userm['email'])->subject($userm['mail_subject']);
        });
        
        
        \Log::info('Invite Friend Mail Response :');
        
        $res=\General::success_res('Invitation Request Sent Successfuly');
        return \Response::json($res, 200);
        
    }

    public function postInviteFriendPushNotify(){
        
        $user = app("logged_in_user");
        $param = \Input::all();
        $param['user_id'] = $user['id'];
        $settings=app('settings');
        \Log::info('Invite Friend Push Notify : ' . json_encode(\Input::all()));
        
        $validator = \Validator::make(\Input::all(), \Validation::get_rules("user", "play_invitation"));
        
        if($validator->fails()) {
            $messages = $validator->messages();
            $error = $messages->all();
            $json = \General::validation_error_res();
            $json['data'] = $error;
            $json['msg'] = $error[0];
            \Log::info('Invite Friend Push Response : ' . json_encode($json));
            return \Response::json($json, 200);
        }
        

        $emails=json_decode($param['emails']);
        if(!is_array($emails)){
              $json = \General::error_res('emails are in array format');
              return \Response::json($json, 200);
        }

        $robots = \App\Models\Users::whereIn('email',$emails)->where('robot_status',1)->get()->toArray();
        $robots=\General::get_field_array($robots,'email');
        foreach($robots as $rm){
        
            $objG = new \App\Http\Controllers\GeneralController;
            $res=$objG->acceptChallenge($param['challenge_id'],$rm);
        
        }
        
        if(count($emails)<1){
            $res = \General::error_res('Atleast One Email Address Required');
            return \Response::json($res, 200);
        }

        $user_list=\App\Models\Users::select('id','device_type','device_token')->active()->whereIn('email',$emails)->get()->toArray();
        
        foreach ($user_list as $send_user){
            
            
            if($send_user['id'])
            $av_path='';
            if($user['avatar']==''){
                $av_path=url("assets/images/my_avatar.jpg");
            }else{
                $av_path=config('constant.USER_AVATAR_PATH_LINK').'/'.$user['avatar'];
            }
            $meta = array();
            $device_tokens = array($send_user['device_token']);
            $msg = array($param['msg']);
            $title = array("New MATcHUP Challenge from ".$user['username']);
            $badge = array(1);
            $screen = array($param['challenge_id']);
            $meta = array(0);
            $img = array($av_path);
            $res = \App\Lib\FCM::send_notification_with_data($device_tokens,$msg,$send_user['device_type'],$title);  
        }
        
        $resChMsg=  \App\Models\MultiPlayerChallenge::where('id',$param['challenge_id'])->update(['challenger_msg'=>$param['msg']]);

        if($resChMsg<0){
             $res=\General::error_res('Opps ! something might wrong');
             return \Response::json($res, 200);
        }
        
        
        $notify_uids=\General::get_field_array($user_list,'id');
        
        foreach ($notify_uids as $nu){
            $nParam['user_id']=$nu;
            $nParam['notify_msg']="New Challenge from ".$user['username'];
            $nParam['type']=0;
            $nParam['challenge_id']=$param['challenge_id'];
            $nParam['responder_user_id']=$user['id'];
            $resNotify=\App\Models\Notifications::add_notification($nParam);
        }
        
        \Log::info('Invite Friend Push Response :');
        
        $nres=\App\Models\MultiPlayerChallenge::where('id',$param['challenge_id'])->update(['friend_invite_status'=>1]);

        if($nres<0){
            $res=\General::error_res('Opps ! something might wrong');
            return \Response::json($res, 200);            
        }
        $res=\General::success_res('Request Sent Successfuly');
        return \Response::json($res, 200);
        
    }
    
     public function postNotificationList(){
        
        $user = app("logged_in_user");
        \Log::info('User Notification List Request : '.json_encode(\Input::all()));
        
        $param = \Input::all();
        $param['user_id'] = $user['id'];
//        $param['last_notification_call'] = $user['last_notification_call'];
        
        
        $validator = \Validator::make(\Input::all(), \Validation::get_rules("pagination", "api_pagination"));
        
        if($validator->fails()) {
            $error = $validator->messages()->all();
            $json = \General::validation_error_res("Please Fill required filled");
            $json['data'] = $error;
//            \Log::info('Notification List Response: ' . json_encode($json));
            return \Response::json($json, 200);
        }
        
        
        // \Log::info('user id: '.$param['user_id']);
        $data = \App\Models\Notifications::get_notification_report($param);
        // dd($data);
        // \Log::info($data);
        if ($data['flag'] == 1) {
            $res = \General::success_res('Notification List');
            unset($data['flag']);
            $res['data'] = $data;
            
            \Log::info('User Notification List  Response : '.json_encode($res));
            return \Response::json($res, 200);
        }

       $user=  \App\Models\Users::where('id',$user['id'])->first();
       $user->last_notification_call=date('Y-m-d H:i:s');
       $user->save();
        
        $res = \General::error_res('Opps ! something Might wrong');
//        \Log::info('User Notification List  Response : '.json_encode($res));
        return \Response::json($res, 200);  
        

    }
    
    
    public function postUpdateNotification(){
        
        $user = app("logged_in_user");
        \Log::info('Update Notification Request : ' . json_encode(\Input::all()));
        
        $validator = \Validator::make(\Input::all(), \Validation::get_rules("user", "update_notification"));
        
        if ($validator->fails()) {
            $messages = $validator->messages();
            $error = $messages->all();
            $json = \General::validation_error_res();
            $json['data'] = $error;
            $json['msg'] = $error[0];
            \Log::info('Update Notification Response : ' . json_encode($json));
            return \Response::json($json, 200);
        }
        
        $param = \Input::all();
        $param['user_id'] = $user['id'];
        $res = \App\Models\Notifications::update_notification($param);
       
        \Log::info('Update Notification Response : ' . json_encode($res));
        return \Response::json($res, 200);
    }
    
    
    public function postDeleteNotification(){
        
        $user = app("logged_in_user");
        \Log::info('Delete Notification Request : ' . json_encode(\Input::all()));
        
        $param = \Input::all();
        $param['user_id'] = $user['id'];
        
        $validator = \Validator::make(\Input::all(), \Validation::get_rules("user", "delete_notification"));
        
        if ($validator->fails()) {
            $messages = $validator->messages();
            $error = $messages->all();
            $json = \General::validation_error_res();
            $json['data'] = $error;
            $json['msg'] = $error[0];
            \Log::info('Delete Notification Response : ' . json_encode($json));
            return \Response::json($json, 200);
        }

        $res = \App\Models\Notifications::delete_notification($param);
        
        
        \Log::info('Delete Notification Response : ' . json_encode($res));
        return \Response::json($res, 200);
        
    }
    
    
    public function postClearAllNotification(){
        
        $user = app("logged_in_user");
        \Log::info('Clear all Notification Request : ' . json_encode(\Input::all()));
        
        $param = \Input::all();
        $param['user_id'] = $user['id'];
        
//        $validator = \Validator::make(\Input::all(), \Validation::get_rules("user", "clear_notification"));
//        
//        if($validator->fails()) {
//            $error = $validator->messages()->all();
//            $json = \General::validation_error_res("Please Fill required filled");
//            $json['data'] = $error;
////            \Log::info('Notification List Response: ' . json_encode($json));
//            return \Response::json($json, 200);
//        }
        
//        $notify_ids=json_decode($param['notification_ids']);
//        if(count($notify_ids)<1){
//             $json = \General::error_res("atleast 1 id is requried");
//             return \Response::json($json, 200);
//        }
        
        $res = \App\Models\Notifications::where('user_id',$param['user_id'])->delete();
        if($res<1){
            $res=\General::error_res('Opps ! Something might wrong');
            return \Response::json($res, 200);
        }
        
        $res=\General::success_res('Notification cleared Successfully');
        
        \Log::info('Clear all Notification Response : ' . json_encode($res));
        return \Response::json($res, 200);
        
    }
    public function postUpdateProfilePic(){
        $user = new \App\Models\Users();
        return $user->updateProfilePic(\Request::all());
    }

}
