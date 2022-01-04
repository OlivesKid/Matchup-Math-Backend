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

class UserController extends Controller {

    private static $bypass_url = ['getIndex', 'getLogin','getEmailVerify' ,'postLogin', 'getResetPassword', 'getForgotPass', 'postForgotPass', 'postResetPassword', 'getSignup', 'postSignup', 'getAuthGoogle', 'redirectGoogle', 'getAuthFacebook', 'redirectFacebook'];

    public function __construct() {
        $this->middleware('UserAuth', ['except' => self::$bypass_url]);
    }

    public function getIndex() {
        
        if (!\Auth::guard('user')->check()) {
//            return \Response::view('errors.401', array(), 401);
            return \Redirect::to('/');
        }
        
        return \Redirect::to('/');
    }
    
    public function NotificatioArray(){

        $Notification['ThreeDay'] = ['Keeping mentally active can help maintain cognitive function. So can sleep. Play MatchUp daily and dream about playing when sleeping.',
            'Math trains the brain and builds the neural pathways that make the brain stronger for all other things. MatchUp Math, we are a mental gym for your brain.',
            'We don’t know if your friends can beat you in game of MatchUp Math but we suggest you challenge them to find out. Play a game of MatchUp today!',
            'Advanced level math adds 11% to earnings, study finds. No other A-level subject attracted such a wage premium. MatchUp Math; we’d like to help you earn more!',
            'Did you know Mount Everest weighs an estimated 357 trillion pounds? Math helps discover amazing things. Play MatchUp and discover your brilliance.',
            'Math is one of the true breakout skill areas that can lead to infinite success in a variety of areas. MatchUp Math, a fun way to develop your math skills.'
            ];
        $Notification['InWeek'] = ['Are you having fun playing MatchUp Math? Please share your enjoyment with friends on your social media pages.'];

        $Notification['InMonth'] = ['Hello (user name), you haven’t played MatchUp in a while. We’ve missed you. Would you like to play now?'];

        $Notification = json_encode($Notification);
        return $Notification;
                
    }
    public function getEmailVerify($token = ''){

        $pass_token = \App\Models\Token::active()->where('token', '=', $token)->get()->toArray();

        if (count($pass_token) <= 0) {
            return \Response::view('errors.404', array('msg'=>'This Link is Expired.!'), 404);
        }

        $token = \App\Models\Token::where('type',\Config::get("constant.ACCOUNT_ACTIVATION_TOKEN_STATUS"))->where('token',$token)->first();
        

        if(!is_Null($token)){

            $user = \App\Models\Users::where('id',$token->user_id)->first();

            if(is_Null($user)){
               
                return \Response::view('errors.404', array('msg'=>'This Link is Invalid.!'), 404);
            }
            
            
            
        $token->delete();
            
//        $age=\General::count_age($user->dob); 
//        $curLevel=  \App\Models\GameLevelRules::where('age','>=',$age)->orderBy('age','asc')->first();
//        if(is_null($curLevel)){
//             return \Response::view('errors.404', array('msg'=>'Opps ! Something might wrong.!'), 404);
////             return \General::error_res('Game level not found for your age range!');
//        }
            

        $sparam['user_id']=$user->id;
        $sparam['current_game_level']=1;
        
        $resSingleUser=  \App\Models\SinglePlayerStatics::where('user_id',$user->id)->first();
        if(is_null($resSingleUser)){
            $resSingleStatics=\App\Models\SinglePlayerStatics::add_single_player_statics($sparam);
            if($resSingleStatics['flag']!=1){
                 return \Response::view('errors.404', array('msg'=>'Opps ! Something might wrong.!'), 404);
            }
        }
            
            
        $resMultiUser=\App\Models\MultiPlayerStatics::where('user_id',$user->id)->first();
        if(is_null($resMultiUser)){
             $resMultiStatics=\App\Models\MultiPlayerStatics::add_multi_player_statics($sparam);
             if($resMultiStatics['flag']!=1){
                 return \Response::view('errors.404', array('msg'=>'Opps ! Something might wrong.!'), 404);
             }
        }
            
            
           
           $user->status = 1;
           $user->save();
           
//            $userm = $user->toArray();
//            $userm['mail_subject'] = 'Welcome to '.config('constant.PALTFORM_NAME');

//            \Mail::send('emails.user.welcome_mail', $userm, function ($message) use ($userm) {
//                $message->to($userm['email'])->subject('Welcome to '.config('constant.COIN_NAME'));
//            });
            
            return view('user.email_verify');
        }
       
        return \Response::view('errors.404', array(), 404);
    }
    
    
    
    public function getResetPassword($token = '') {
//        dd($token);
        $pass_token = \App\Models\Token::active()->where('token', '=', $token)->get()->toArray();
        
        if (count($pass_token) <= 0) {

            $view_data = [
            'header' => [
                "title" => "Reset Password",
                "js"    => [],
                "css"   => []
            ],
            'body' => [
//                'forgorttoken' => null,
            ],
            'footer' => [
                "js"    => [],
                "css"   => [],
                'flag'  => 1,
            ],
        ];
            
            $res['msg'] = 'This Link is Expired.';
            return \Response::view('errors.404', array('msg'=>'This Link is Expired.'), 404);
        }

//        dd($pass_token);


        $view_data = [
            'header' => [
                "title" => "Forgot Password",
                "js"    => [],
                "css"   => []
            ],
            'body' => [
                'forgorttoken' => $token,
            ],
            'footer' => [
                "js"    => [],
                "css"   => [],
                'flag'  => 1,
            ],
        ];

        return view('user.reset_password',$view_data);
    }

    public function postResetPassword() {
        $param = \Input::all();
//        dd($param);
        
        $custom_msg=[
            'new_pass.required'=>'Fill New Password',
            'cnew_pass.required'=>'Fill Confirm Password',
        ];
        $view_data_back = [
            'header' => [
                "title" => "Reset Password",
                "js"    => [],
                "css"   => []
            ],
            'body' => [
                'forgorttoken' => $param['forgottoken'],
            ],
            'footer' => [
                "js"    => [],
                "css"   => [],
                'flag'  => 1,
            ],
        ];
       
        $validator = \Validator::make(\Input::all(), \Validation::get_rules("user", "reset_pass"),$custom_msg);
        if ($validator->fails()) {
            $messages = $validator->messages();
            $error = $messages->all();
            return view('user.reset_password', $view_data_back)->withErrors($validator);
        }
        
        if($param['new_pass'] != $param['cnew_pass']){
            return view('user.reset_password', $view_data_back)->withErrors(['msg' => 'New Password and Confirm Password not Matched.' ]);
        }
        
//                
        $user = \App\Models\Token::where('type',\Config::get("constant.FORGETPASS_TOKEN_STATUS"))->where('token',$param['forgottoken'])->first();
        if(!is_Null($user)){
//            dd($param,$user->toArray());
            $userInfo = \App\Models\Users::where('id',$user->user_id)->first();
            if(is_Null($userInfo)){
                return view('user.reset_password', $view_data_back)->withErrors(['msg' => 'User Not Found.' ]);
            }
            $userInfo->password =\Hash::make($param['new_pass']);
            $userInfo->save();
            $user->delete();
        }

            return view('user.success_reset_password',[]); 
        
    }
    
    
}