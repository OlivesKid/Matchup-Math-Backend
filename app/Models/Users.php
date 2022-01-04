<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;
use Illuminate\Http\Exception\HttpResponseException;

class Users extends Model implements Authenticatable {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    use AuthenticableTrait;
    
    public function getAuthIdentifier() {
        return $this->getKey();
    }

    public function getAuthIdentifierName() {
        return $this->getKeyName();
    }

    public function getAuthPassword() {
        return $this->password;
    }

    public function getRememberToken() {
        return $this->{$this->getRememberTokenName()};
    }

    public function getRememberTokenName() {
        return 'remember_token';
    }

    public function setRememberToken($value) {
        $this->{$this->getRememberTokenName()} = $value;
    }

//    use Authenticatable, CanResetPassword;
    protected $fillable = [
        'name', 'email', 'password','status', 'dob', 'avatar', 'last_ip','last_ua', 'device_type','device_token',
    ];
    
    protected $table = 'users';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'remember_token','password'
    ];
    
    
    // protected $appends = ['avatar_dummy'];
    // function getAvatarDummyAttribute() {

    //     if($this->avatar==''){
    //         return self::preImage();
    //     }else{
    //         return config('constant.USER_AVATAR_PATH_LINK').'/';
    //     }
    // }


     public function getAvatarAttribute($value){
        if($value==''){
            return config('constant.USER_AVATAR_PATH_LINK').'/'.config('constant.DUMMY_AVATAR_IMG');
        }else{
            return config('constant.USER_AVATAR_PATH_LINK').'/'.$value;
        }
    }

//    public function user(){
//        return $this->hasOne('App\Models\Users','id','user_id');
//    }
    
    public function userScore(){
        return $this->hasOne('App\Models\MultiPlayerStatics','user_id','id');
    }
    
    public function userMail(){
        return $this->hasOne('App\Models\Users','id','id');
    }
    
    public static function preImage(){
        return \URL::to("assets/images/my_avatar.jpg");
    }
    
    public function scopeActive($query) {
        return $query->where('status', '=', 1);
    }
    
    public static function doLogin($param){
//        dd($param);
        if(isset($param['remember']))
        {
            \Cookie::get("remember",1);
            if($param['remember']=='on')
                $param['remember']=1;
            else
                $param['remember']=0;
//            \App\Models\Admin\Settings::set_config(['sanitize_input' => $param['remember']]);
        }
        $user = self::where("email", $param['user_email'])->first();
        $res['data']=$user;
        
//        dd($user->toArray());
        
        $res['flag']=0;
        if (is_null($user)) {
            $res['flag']=0;
            return $res;
        }
        if ($user->password != $param['user_pass']) {
            $res['flag']=0;
            return $res;
        }
        if ($user->status == 2) {
            $res['flag'] =4; 
//           
            return $res;
        }
     
       
        
        if(isset($param['remember']) && $param['remember']==1)
        {
            $auth_token = \App\Models\Token::generate_auth_token();
            
//            $token_data = ['user_id' => $user->id,'token' => $auth_token,'type' => 'auth'];
            $token_data = ['user_id' => $user->id,'token' => $auth_token,'type' => \Config::get("constant.AUTH_TOKEN_STATUS")];
            \App\Models\Token::save_token($token_data);
            \Auth::guard("user")->loginUsingId($user->id,true);
        }
        else{
            $auth_token = \App\Models\Token::generate_auth_token();
            
//            $token_data = ['user_id' => $user->id,'token' => $auth_token,'type' => 'auth'];
            $token_data = ['user_id' => $user->id,'token' => $auth_token,'type' => \Config::get("constant.AUTH_TOKEN_STATUS")];
            \App\Models\Token::save_token($token_data);
            \Auth::guard("user")->loginUsingId($user->id);
        }
        
        $res['flag']=1;
        return $res;
    }
    
    public static function do_login($param) {
//        dd($param);
        if (($param['type'] == "facebook" || $param['type'] == "google") && (!isset($param['access_token']) || $param['access_token'] == "")) {
            return \General::error_res("access_token_missing");
        }
        if ($param['type'] == "facebook") {
            $res = \App\Models\Services\General::check_facebook_access_token($param['access_token']);
            if ($res['flag'] != 1)
                return $res;
            $param['user_email'] = $res['data']['email'];
        } 
        elseif ($param['type'] == "google") {
            $res = \App\Models\Services\General::check_google_access_token($param['access_token']);
            if ($res['flag'] != 1)
                return $res;
            $param['user_email'] = $res['data']['email'];
        }
        
       $user = self::where("email", $param['user_email'])->first();
       if(is_null($user) && $param['type']='normal'){
          return \General::error_res("User not found.");
       }
        if($param['type']!='normal'){
            if (is_null($user)) {
                $data = ['email' => $param['user_email'], "password" => \General::rand_str(5), "name" => isset($param['name']) ? $param['name'] : "","mobile" => isset($param['mobile']) ? $param['mobile'] : "","parent_id" => "","device_token" => $param['device_token']];
                if ($param['type'] == "facebook") {
                    $data['fb_status'] = 1;
                } else if ($param['type'] == "google") {
                    $data['g_status'] = 1;
                }


                self::signup($data);

                $user = self::where("email", $param['user_email'])->first();
            }
        }
     
        if ($param['type'] == "normal" && !\Hash::check($param['user_pass'],$user->password)) {
            
            return \General::error_res("Username or password is incorrect.");
        }
        if (($param['type'] == "normal") && $user->status == \Config::get("constant.USER_PENDING_STATUS")) {
            return \General::error_res("Please verify your email to finish signing up for MatchUp Math.");
        }
        if ($user->status == \Config::get("constant.USER_SUSPEND_STATUS")) {
              
            return \General::error_res("account_suspended");
        }
        $user->device_token = $param['device_token'];
        $user->device_type = app("platform");
        $user->save();
        \Log::info('device token:'.$param['device_token']);
        //Delete User Auth Tokens
        // $delete_token = \App\Models\Token::delete_token();
//        $dead_token_id = \App\Models\Token::find_dead_token_id('auth', $user->id);
        $dead_token_id = \App\Models\Token::find_dead_token_id(\Config::get("constant.AUTH_TOKEN_STATUS"), $user->id);
        $platform = app("platform");
        $token = \App\Models\Token::generate_auth_token();
        if ($token == "")
            return \General::error_res("try_again");

        $data = ["type" => \Config::get("constant.AUTH_TOKEN_STATUS"), "platform" => $platform, "user_id" => $user->id, "token" => $token, "ip" => \Request::getClientIp(), "ua" => \Request::server("HTTP_USER_AGENT")];
//        $data = ["type" => 'auth', "platform" => $platform, "user_id" => $user->id, "token" => $token, "ip" => \Request::getClientIp(), "ua" => \Request::server("HTTP_USER_AGENT")];

        if ($dead_token_id) {
            $data['id'] = $dead_token_id;
        }
         \App\Models\Token::delete_all_auth_token($user->id);
        \App\Models\Token::save_token($data);
        $user_data = $user->toArray();
//        $user_data['avatar'] = self::get_image_url($user['id'],$user['avatar']);
        $user_data['auth_token'] = $token;
       
        
//        $user_data['mobile'] = \App\Models\Services\General::formate_mobile_no($user_data['mobile']);
        unset($user_data['password']);
        if(!\Request::wantsJson())
        {
            \Auth::guard("user")->loginUsingId($user['id']);
        }
        
        $res = \General::success_res('');
        $user_data=collect($user_data);
        
        $user_data=$user_data->only(['id','email','username','dob','avatar','auth_token'])->all();
        $mySingleGame  = SinglePlayerStatics::getStaticsByUserId($user_data['id']);
        $user_data['level']=  GameLevelRules::getByLevelApi($mySingleGame['current_game_level']);          
        $res['data'] = $user_data;
        return $res;
    }

    
    public static function get_user_list($param){
        
        // dd($param);
        $count=self::orderBy('id','desc');
        if(isset($param['search']) && $param['search']!=''){
            $count=self::where('username','like','%'.$param['search'].'%');
        }
        if(isset($param['status']) && $param['status']!=''){
            $count = $count->where('status',$param['status']);
        }
        $count = $count->count();
        $page=$param['crnt'];
        $len=$param['len'];
        $op=$param['opr'];
        $total_page=ceil($count/$len);
        $flag=1;
        
        $start;
        
        if($op!=''){
            if($op=='first'){
                $crnt_page=1;
                $start=($crnt_page-1)*$len;
            }
            
            elseif($op=='prev'){
                $crnt_page=$page-1;
                if($crnt_page<=0){
                    $crnt_page=1;
                }
                $start=($crnt_page-1)*$len;
            }

            elseif($op=='next'){
                $crnt_page=$page+1;
                if($crnt_page>=$total_page){
                    // $crnt_page=$total_page;
                }
                $start=($crnt_page-1)*$len;
            }

            else{
                $crnt_page=$total_page;
                $start=($crnt_page-1)*$len;
            }
        }

        else{
            if($page>$total_page){
//                $flag=0;
                $crnt_page=$page-1;
                $start=($crnt_page-1)*$len;
            }
            else{
                $crnt_page=$page;
                $start=($crnt_page-1)*$len;
            }
        }
        
//        dd(config('constant.SP_TYPE'));
        $udata=self::orderBy('id','desc');
        
        if(isset($param['search']) && $param['search']!=''){
            $crnt_page=1;
            $start=($crnt_page-1)*$len;
            $udata=self::where('username','like','%'.$param['search'].'%');
        }
        if(isset($param['status']) && $param['status']!=''){
            $udata = $udata->where('status',$param['status']);
        }
        $udata = $udata->skip($start)->take($len)->get()->toArray();
        
        $res['len']=$len;
        $res['crnt_page']=$crnt_page;
        $res['total_page']=$total_page;
        
        $res['result']=$udata;
        $res['flag']=$flag;
        return $res;
    }
    
    public static function edit_user($param){
        if(isset($param['id'])){
            $u = self::where('id',$param['id'])->first();
            if(is_null($u)){
                return \General::error_res('no user found');
            }
            if(isset($param['status'])){
                $u->status = $param['status'];
            }
            $u->save();
            $res = \General::success_res('user edited successfully !!');
            $res['data'] = $u;
            return $res;
        }else{
            return \General::error_res('no user found');
        }
    }
    
    public static function delete_user($param){
        if(isset($param['id'])){
            $u = self::where('id',$param['id'])->first();
            if(is_null($u)){
                return \General::error_res('no user found');
            }
            $u = self::where('id',$param['id'])->delete();
            $res = \General::success_res('user deleted successfully !!');
            return $res;
        }else{
            return \General::error_res('no user found');
        }
    }
    
    
     public static function get_user($param){
        $users = self::orderBy('username','asc');
        if(isset($param['name']) && $param['name'] != ''){
            $users = $users->where('username','like','%'.$param['name'].'%');
        }
        $users = $users->get()->toArray();
        
        return $users;
    }
    
    public static function signup($param) {
//        dd($param);

        $user = new Users();
        $user->username    = $param['username'];
        $user->email    = $param['email'];
        $user->password = \Hash::make($param['password']);
        $user->dob = $param['dob'];
        // $user->avatar = config('constant.DUMMY_AVATAR_IMG');
//        $user->mobileno =$param['mobile']!=''?$param['mobile']:null;
        $user->status   = \Config::get("constant.USER_PENDING_STATUS");
        $user->last_ip      = \Request::getClientIp();
        $user->last_ua      = \Request::server("HTTP_USER_AGENT");
        $user->last_notification_call      = date('Y-m-d H:i:s');
        $user->device_token = $param['device_token'];
        $user->device_type  = app("platform");
        $user->save();


        $activation_token = \App\Models\Token::generate_activation_token();
        $user['activation_token'] = $activation_token;
        $data = ['status' => 1, 'type' => \Config::get("constant.ACCOUNT_ACTIVATION_TOKEN_STATUS"), 'platform' => app("platform"), 'user_id' => $user->id, 'token' => $activation_token, "ip" => \Request::getClientIp(), "ua" => \Request::server("HTTP_USER_AGENT")];
        $user_obj = $user;
        $user = $user->toArray();
        \App\Models\Token::save_token($data);


        unset($user['password']);
        $user['to_name']=ucwords($param['username']);
        $userm['from']=config('constant.SUPPORT_MAIL');
        $userm['name']=config('constant.PLATFORM_NAME');
        $userm['email']=$user['email'];

        
        
        $user['mail_subject']='Email verification Request';
        \Mail::send('emails.user.signup_mail', $user, function ($message) use ($userm) {
            $message->from($userm['from'],$userm['name'])->to($userm['email'])->subject('New Signup Request');
        });
        
        
//        $age=\General::count_age($param['dob']); 
//        $curLevel=  \App\Models\GameLevelRules::where('age','>=',$age)->orderBy('age','asc')->first();
//        if(is_null($curLevel)){
//             return \General::error_res('Game level not found for your age range!');
//        }
        
        
        $sparam['user_id']=$user['id'];
        $sparam['current_game_level']=1;
        $resSingleUser=  \App\Models\SinglePlayerStatics::where('user_id',$user['id'])->first();

        if(is_null($resSingleUser)){
            $resSingleStatics=\App\Models\SinglePlayerStatics::add_single_player_statics($sparam);
            if($resSingleStatics['flag']!=1){
                return \General::error_res('Opps ! Something might wrong.!');
            }
        }
            

        $resMultiUser=\App\Models\MultiPlayerStatics::where('user_id',$user['id'])->first();
        if(is_null($resMultiUser)){
             $resMultiStatics=\App\Models\MultiPlayerStatics::add_multi_player_statics($sparam);
             if($resMultiStatics['flag']!=1){
                return \General::error_res('Opps ! Something might wrong.!');
             }
        }
        
        $res = \General::success_res("We've sent account verification link to your email address.Please click on the link given in email to verify your account.");
        $res['data']=[];
        $res['signup_time'] = $user['created_at'];
        return $res;
    }
    
    public static function is_logged_in($token) {
        
        if (\Request::wantsJson()) {
           
            if ($token == "") {
                return \General::session_expire_res();
            }
//            $already_login = \App\Models\Token::is_active('auth', $token);
            $already_login = \App\Models\Token::is_active(\Config::get("constant.AUTH_TOKEN_STATUS"), $token);
           
            if ( $already_login===false){
                 
                return \General::session_expire_res("unauthorise");
            }
            else {
                
                $user = \App\Models\Users::where("id", $already_login)->first()->toArray();
                unset($user['password']);
                $user['auth_token'] = $token;
                app()->instance('logged_in_user', $user);
                return \General::success_res("");
            }
        } else {
           
            if (!\Auth::guard('user')->check()) {
                
                \Auth::guard('user')->logout();
                $validator = \Validator::make([], []);
               
                $validator->errors()->add('attempt', \Lang::get('error.session_expired', []));
                return \General::session_expire_res("unauthorise");
                
                
            } else {
                
                $user_data = \Auth::guard('user')->user();
                $user=$user_data->toArray();


                $ua = \Request::server("HTTP_USER_AGENT");
                $ip = \Request::server("REMOTE_ADDR");


                $session = \App\Models\Token::active()->where("type", \Config::get("constant.AUTH_TOKEN_STATUS"))->where("ua", $ua)->where("ip", $ip)->where("user_id", $user['id'])->first();
                if (is_null($session)) {
                    \Auth::guard('user')->logout();
                    $user['auth_token'] = "";
                } else {
                    $user['auth_token'] = $session['token'];
                }
                app()->instance('logged_in_user', $user);
            }
        }
        return \General::success_res();
    }
    
    
        
    public static function get_image_url($id, $file_name = "") {
        $default_path   = \URL::to("assets/img/nobody_m.jpg");
        $file_path      = 'assets/uploads/avatar/'. $file_name;
       
        if ($file_name != '' && file_exists(public_path().'\\' . $file_path1))
        {
            $file_url = asset($file_path);
        }
        else
        {
            $file_url = asset($default_path);
        }
        return $file_url;
    }

    
    
    public static function update_profile($param) {
        
        $id = $param['user_id'];
        $user = self::where("id", $id)->first();
    
        if(is_null($user)){
            return \General::error_res("User Not Found");
        }
        
        if(isset($param['avatar']) && $param['avatar'] != ""){
        
                if(\Input::hasFile('avatar')) {
                    
                    $old_avatar=basename($user->avatar);        
                    $dir_path = \Config::get('constant.USER_AVATAR_PATH');
                    
                    if (!file_exists($dir_path)) {
                        mkdir($dir_path, 0777, true);
                    }
                    
                        
                    $ext = \Input::file('avatar')->getClientOriginalExtension();
                    if(!in_array(strtolower($ext), ["jpg","jpeg","png"]))
                    {
                        return \General::error_res("File must be image");
                    }

                    $fileName = time() .rand()."." . $ext;

                    \Input::file('avatar')->move(\Config::get('constant.USER_AVATAR_PATH'), $fileName);
                   
                    $user->avatar = $fileName;
                
                    // \Log::info($dir_path.$old_avatar);
                    if($old_avatar!='' && $old_avatar!=config('constant.DUMMY_AVATAR_IMG')){
                        if(is_file($dir_path.$old_avatar)){
                            unlink($dir_path.$old_avatar);    
                        }
                    }
                    
                    
                } else {
                    unset($param['avatar']);
                }
        }

        

        if (isset($param['username'])){
            $user->username = $param['username'];
        }
          
        if (isset($param['dob'])){
            $user->dob = $param['dob'];
        }
        
 
        $user->last_ip = \Request::getClientIp();
        $user->save();
        
        $res = \General::success_res("Profile Updated");
        $user_data = $user->toArray();
      
        
        $user_data=collect($user_data)->only(['username','avatar'])->all();
        $res['data'] = $user_data;
        return $res;
        
    }
    
    public static function change_password($param){
       
        $user = self::where("id", $param['user_id'])->first();
     
        if (is_null($user)) {
            return \General::error_res("User not found.");
        }
        
        if ($user->status == config("constant.USER_SUSPEND_STATUS")) {
            return \General::error_res("Account is Suspended");
        }
        
        if(!\Hash::check($param['old_password'],$user->password)){
            return \General::error_res("Incorrect password.");
        }
        
        if($param['new_password'] != $param['confirm_password']){
            return \General::error_res("New and Confirm password do not match.");
        }
        
        // \Log::info('Old Password : '.$user->password);
        $user->password =\Hash::make($param['new_password']);
        $user->save();
        // \Log::info('New Password : '.$user->password);
        $res=\General::success_res("Password updated successfully.");
        $user=$user->toArray();
        $res['data']['last_updated_pwd_time']=$user['updated_at'];
        return $res;
        
    }
    
    
   public static function update_email($param) {
        
        $id = $param['user_id'];
        $user = self::where("id", $id)->first();
        if (is_null($user)) {
            return \General::error_res("User Not Found");
        }
        
        $muser =self::where("id",'!=',$param['user_id'])->where("email",$param['email'])->first();
        
        if(!is_null($muser)) {
            return \General::error_res("Email is Already taken");
        }
     
        if(!\Hash::check($param['password'],$user->password)){
            return \General::error_res("Wrong  password.");
        }

        
        $user->email = $param['email'];
        $user->last_ip = \Request::getClientIp();
        $user->save();
        
        $res = \General::success_res("Email Updated");
        $user_data = $user->toArray();
       
        unset($user_data['password']); 
        $res['data'] = $user_data;
        return $res;
        
    }
   
    public static function change_user_status($param){
        $user_id = $param['user_id'];
        $status = $param['status'];

        $user = self::where('id',$user_id)->first();
        if(!$user){
            return \General::error_res('no user found');
        }
        
        $user->status = $status;
        $user->save();
        
        if($status != 1){
            Token::where('user_id',$user_id)->where('type',config('constant.AUTH_TOKEN_STATUS'))->delete();
        }
        
        return \General::success_res('status changed successfully');
    }
     public static function change_user_robot_status($param){
        $user_id = $param['user_id'];
        $status = $param['status'];

        $user = self::where('id',$user_id)->first();
        if(!$user){
            return \General::error_res('no user found');
        }
        
        $user->robot_status = $status;
        $user->save();
        
        return \General::success_res('Robot status changed successfully');
    } 
    public static function edit_user_detail($param){
        $user_id = $param['user_id'];
        
        $user = self::where('id',$user_id)->first();
        if(!$user){
            return \General::error_res('no user found.');
        }
        
        $user->username = $param['user_name'];
//        $user->email = $param['user_email'];
     
       
        $user->save();
        
        return \General::success_res('user updated successfully');
    }
    
    public static function forget_password($param) {
        $user = self::where("email", $param['email'])->first();
        if (is_null($user)) {
            return \General::error_res("Email does not exist.");
        }
        if ($user->status == config("constant.USER_SUSPEND_STATUS")) {
            return \General::error_res("Account is Susspended");
        }
        
        $platform = app("platform");
        $user_detail = $user->toArray();
        
        $forgotpass_token = \App\Models\Token::generate_forgotpass_token();
        $user_detail['forgotpass_token'] = $forgotpass_token;
//        dd(\Config::get("constant.FORGETPASS_TOKEN_STATUS"));
        $data = ['status' => 1, 'type' => \Config::get("constant.FORGETPASS_TOKEN_STATUS"), 'platform' => app("platform"), 'user_id' => $user->id, 'token' => $forgotpass_token, "ip" => \Request::getClientIp(), "ua" => \Request::server("HTTP_USER_AGENT")];
        
        $token = \App\Models\Token::save_token($data);
//        dd($token);
        $user_detail['mail_subject'] = 'Password reset request';
        $user_detail['mail_from_email'] =\Config::get('constant.SYSTEM_EMAIL');
        $user_detail['mail_from_name'] =\Config::get('constant.SYSTEM_EMAIL_NAME');
//        dd($user_detail);
//        echo \View::make("emails.user.forget_password",$user_detail)->render();
//        exit;

        \Mail::send('emails.user.forget_password', $user_detail, function ($message) use ($user_detail) {
            $message->from($user_detail['mail_from_email'],$user_detail['mail_from_name'])->to($user_detail['email'])->subject($user_detail['mail_subject']);
        });
        
        return \General::success_res("Please check your email inbox.");
    }
    
    public static function resend_confirmation($param) {
        $user = self::where("email", $param['email'])->first();
        if (is_null($user)) {
            return \General::error_res("Email does not exist.");
        }
        if ($user->status == config("constant.USER_SUSPEND_STATUS")) {
            return \General::error_res("Account is suspended");
        }
        
        $platform = app("platform");
        $user_detail = $user->toArray();
        


        $activation_token = \App\Models\Token::generate_activation_token();
        $user_detail['activation_token'] = $activation_token;
        


        $data = ['status' => 1, 'type' => \Config::get("constant.ACCOUNT_ACTIVATION_TOKEN_STATUS"), 'platform' => app("platform"), 'user_id' => $user->id, 'token' => $activation_token, "ip" => \Request::getClientIp(), "ua" => \Request::server("HTTP_USER_AGENT")];
        
        $token = \App\Models\Token::save_token($data);

        $user_detail['mail_subject'] = 'Resended Confirmation Mail';

        \Mail::send('emails.user.resend_confirmation', $user_detail, function ($message) use ($user_detail) {
            $message->to($user_detail['email'])->subject('Email Verification Mail');
        });
        
        

        return \General::success_res("Account activation mail Sent.");
    }
    
     public static function filter_users($param){
        
        $users = self::orderBy('id','desc');
//        if(isset($param['name']) && $param['name'] != ''){
//            $users = $users->where(function($q)use($param){
//               $q->where('username','like','%'.$param['name'].'%'); 
//            });
//        }
        
        if(isset($param['user_id']) && $param['user_id'] != ''){
             $users =  $users->where('id',$param['user_id']);
        }
        
        if(isset($param['email']) && $param['email'] != ''){
            $users = $users->where('email','like','%'.$param['email'].'%');
        }
        if(isset($param['status']) && $param['status'] != ''){
            $users = $users->where('status',$param['status']);
        }
        if(isset($param['robot_status']) && $param['robot_status'] != ''){
            $users = $users->where('robot_status',$param['robot_status']);
        }
        $count = $users->count();
        
        $len = $param['itemPerPage'];
        $start = ($param['currentPage']-1) * $len;
        
        $users = $users->skip($start)->take($len)->get()->toArray();
        $res['data'] = $users;
        $res['total_record'] = $count;
        
        return $res;
    }
    
    
    public static function get_field_array($list=array(),$field){
        $tmpArray=array();
        foreach($list as $l)
        {
               array_push($tmpArray,$l[$field]);
        }
        

          return $tmpArray;
    }
    
    public static function live_user_list($param){
        
        $users = self::orderBy('id','desc');
        
        $friend_listIn=\App\Models\FriendsList::active()->where('user_id',$param['user_id'])->get()->toArray();
//        $friend_listOut=  \App\Models\FriendsList::inactive()->where('user_id',$param['user_id'])->get()->toArray();
        
        
//     dd($friend_listIn);
//     dd($friend_listOut);
        
       $tmpF=array();
       $fr_count=count($friend_listIn);
       

       $tmpF=self::get_field_array($friend_listIn,'email');
           
       $liveFriend=array();
       $liveusers1=array();
       $liveusers2=array();
       $lusr=0;
       $len=0;
//     $luser=10-$fr_count;

       
//     \DB::enableQueryLog();

       
       $liveusers1=\App\Models\Users::select('users.id','username','email','avatar')->whereIn('email',$tmpF)->join('users_token', 'users.id', '=', 'users_token.user_id')->where('users_token.status',1)->where('users_token.type',0)->get()->toArray();
     
       $luser=count($liveusers1);
       if($luser<10){
           $len=10-$luser;
           $liveusers2=\App\Models\Users::select('users.id','username','email','avatar')->where('users.id','!=',$param['user_id'])->whereNotIn('email',$tmpF)->join('users_token', 'users.id', '=', 'users_token.user_id')->where('users_token.status',1)->where('users_token.type',0)->inRandomOrder()->take($len)->get()->toArray();          
      }
       
       
       
//        $query = \DB::getQueryLog();
//        $query = end($query);
//        dd($query);
              
//       dd($liveusers);
       

        $res=\General::success_res('Live Friends List');
        $res['data']['live_friends'] = $liveusers1;
        $res['data']['rand_friends'] = $liveusers2; 
        return $res;
        
    }
    
    public static function updateProfilePic($input){
        
        $token = \Request::header('AuthToken');
        if ($token == "") {
            return \Response::json(\General::session_expire_res(), 401);
        }
        $user = app("logged_in_user");
        $user = self::find($user['id'])->first();
        $rules = [
            "uploadFile"    => "required|image|max:5000",
        ];
        \Log::info("rules: ");
        \Log::info($rules);

        \Log::info("Input: ");
        \Log::info(\Request::all());

        $validator = \Validator::make($input,$rules);
        if ($validator->fails()) {
            $messages = $validator->messages();
            $error = $messages->all();
            $json = \General::validation_error_res();
            $json['data'] = $error;
            $json['msg'] = $error[0];
            $res = response()->json($json,422);
            throw new HttpResponseException($res);
        }
        
        $folderName = \Config::get('constant.USER_AVATAR_PATH');
        if (!file_exists($folderName)) {
			mkdir($folderName, 0777, true);
		}
        $image = \Request::file('uploadFile');
        $ext = $image->getClientOriginalExtension();
        $imageFileName =$user->id . '.' . $image->getClientOriginalExtension();
        $moved = $image->move($folderName,$imageFileName);
        
        if($moved){
			$user->avatar=$imageFileName;
			$user->save();
			$finalUrl = asset('assets/uploads/avatar/'.$imageFileName);
			$return  = \General::success_res();
            $return['file'] = $finalUrl;
            return $return;
		}else{
			$return = \General::error_res();
            return $return;
		}
        
        
    }
    static function get_my_robots($user_id=0){
        return  self::select('id','email','status','created_at','updated_at')
                ->where('robot_status',1)
                ->where('id','!=',$user_id)
                ->limit(5)->get()->toArray();
    }

    
}
