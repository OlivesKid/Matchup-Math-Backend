<?php

namespace App\Models;

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use DB;
use FormulaParser\FormulaParser;

/* Triggers tables :multi_player_challenge_player,multi_player_challenge_player_game,single_player_game */

class GameController extends Controller {

    private static $bypass_url = [];

    public function __construct() {

        $this->middleware('APIUserAuth', ['except' => self::$bypass_url]);

        $url = \Route::getCurrentRoute()->getActionName();
        $action_name = explode("@", $url)[1];

        if (in_array($action_name, self::$bypass_url)) {
            $this->middleware('GuestAuth');
        }
    }

    
    public static function updatePlayerLevel($user,$gameType='multi',$score=0){
        $settings= app('settings');
        $tTotalGame=$settings['total_threshold_game'];
        $can_retry = 0;
        
        if($gameType=="multi"){
            $userStat= \App\Models\MultiPlayerStatics::where('user_id',$user['id'])->first();
            $tAvg_query = \App\Models\MultiPlayerChallengePlayer::where('won_status',1)->where('user_id',$user['id']);
            $tAvg= \App\Models\MultiPlayerChallengePlayer::where('won_status',1)->where('user_id',$user['id'])->orderBy('id','DESC')->take($tTotalGame)->get()->avg('t_time');
        }elseif($gameType=="single"){
            $userStat= \App\Models\SinglePlayerStatics::where('user_id',$user['id'])->first();
            $tAvg_query = \App\Models\SinglePlayerGame::where('won_status',1)->where('player_id',$user['id'])->where('skip','=',0);
            $tAvg= \App\Models\SinglePlayerGame::where('won_status',1)->where('player_id',$user['id'])->where('skip','=',0)->orderBy('id','DESC')->take($tTotalGame)->get()->avg('t_time');
        }
        
        if($tAvg_query->count()>=$tTotalGame){
            if($tAvg<$settings['lt'] ){
                if($userStat->current_game_level<30){
                   $userStat->current_game_level=$userStat->current_game_level+1;
                }
            }else if($tAvg >$settings['ut']){
                if($userStat->current_game_level>1){
                    if($score>70){
                        $can_retry=1;
                    }else{
                        $userStat->current_game_level=$userStat->current_game_level-1;
                    }
                }
            }

            try{
                $userStat->save();
            }catch(\Illuminate\Database\QueryException $e){
                return \General::error_res();
            }
        }
        $json =  \General::success_res();
        if($gameType=="single"){
            $json['can_retry']=$can_retry;
        }
        return $json;
    }
    
    
    
    public function postCreateChallenge(){
        
        $user = app("logged_in_user");
        $param = \Input::all();
        $param['user_id'] = $user['id'];
//        \Log::info('Create Game : ' . json_encode(\Input::all()));
        
        $validator = \Validator::make(\Input::all(), \Validation::get_rules("game", "add_game"));
        
        if($validator->fails()) {
            $messages = $validator->messages();
            $error = $messages->all();
            $json = \General::validation_error_res();
            $json['data'] = $error;
            $json['msg'] ="Please Fill Requeried filled.";
//            \Log::info('Invite Friend Push Response : ' . json_encode($json));
            return \Response::json($json, 200);
        }
        
        $res=\App\Models\MultiPlayerChallenge::add_challenge($param);
        
        $game_id=$res['data'];
        if($res['flag']==1){
            $gparam['user_id']=$param['user_id'];
            $gparam['challenge_id']=$res['data'];
            $res=\App\Models\MultiPlayerChallengePlayer::add_challenge_player($gparam);
            $res['data']['challenge_id']=$game_id;
            
        }else{
            
            $res=\General::error_res('Opps ! Something might wrong');
            return \Response::json($res, 200);
        }
        
        
//        \Log::info('Create Game Response :');
        return \Response::json($res, 200);
        
    }
    
    public function postJoinChallengePlayer(){
        
        $user = app("logged_in_user");
        
        $param = \Input::all();
        $param['user_id'] = $user['id'];
//        \Log::info('Join challenge Player : ' . json_encode(\Input::all()));
        
        $validator = \Validator::make(\Input::all(), \Validation::get_rules("game", "gameid"));
        
        if($validator->fails()) {
            $messages = $validator->messages();
            $error = $messages->all();
            $json = \General::validation_error_res();
            $json['data'] = $error;
            $json['msg'] = "Please Fill Requeried filled.";
            \Log::info('Join Friend Response : ' . json_encode($json));
            return \Response::json($json, 200);
        }
        
        $chkChallenge=\App\Models\MultiPlayerChallenge::active()->where('id',$param['challenge_id'])->where('end_time','>',date('Y-m-d H:i:s'))->first();
        if(is_null($chkChallenge)){
                  $res = \General::error_res("Opps ! Challenge is Expired .You can't join this challenge.");
                  return \Response::json($res, 200);
        } 
        
        
        $res=\App\Models\MultiPlayerChallengePlayer::add_challenge_player($param);
        if($res['flag']==1){
           
            $ch=\App\Models\MultiPlayerChallenge::with('user')->where('id',$param['challenge_id'])->first();
            if(is_null($ch)){
                  $json = \General::error_res('Challenger Id not Found');
                  return \Response::json($json, 200);
            }
            
            $send_user=\App\Models\Users::select('id','device_type','device_token')->active()->where('email',$ch->user->email)->first()->toArray();
//            $d_tokens=\General::get_field_array($user_list,'device_token');            
            
            $av_path='';
            if($user['avatar']==''){
                $av_path=\URL::to("assets/images/my_avatar.jpg");
            }else{
                $av_path=config('constant.USER_AVATAR_PATH_LINK').'/'.$user['avatar'];
            }
            
            $meta = array();
            $device_tokens = array($send_user['device_token']);
            $msg = array($user['username']." accepted your challenge");
            $title = array("Challenge Accepted");
            $badge = array(1);
            $screen = array($param['challenge_id']);
            $meta = array(1);
            $img = array($av_path);

            $res = \App\Lib\FCM::send_notification_with_data($device_tokens,$msg,$send_user['device_type'],$title);
            // if($send_user['device_type']==config('constant.ANDROID_APP_DEVICE')){
            //     $resAndroidPush = \App\Lib\Push::android_push($device_tokens,$msg,$title,$badge,$screen,$meta,[],$img);
            // }elseif($send_user['device_type']==config('constant.IPHONE_APP_DEVICE')){
            //     $resApplePush = \App\Lib\Push::iphone_push($device_tokens,$msg,$title,$badge,$screen,$meta,[],$img);
            // }
            
            $nParam['user_id']=$send_user['id'];
            $nParam['notify_msg']=$user['username']." accepted your challenge";
            $nParam['type']=1;
            $nParam['challenge_id']=$param['challenge_id'];
            $nParam['responder_user_id']=$user['id'];
            $resNotify=\App\Models\Notifications::add_notification($nParam);

            
            $resNot=\App\Models\Notifications::where('user_id',$user['id'])->where('challenge_id',$param['challenge_id'])->where('type',0)->first();
            if(!is_null($resNot)){
                $resNot->delete();
            }
 
        }
        
//        \Log::info('Join challenge Player :'.json_encode($res));
        return \Response::json($res, 200);
        
    }
    
    public function postRejectChallenge(){
        
        $user = app("logged_in_user");
        
        $param = \Input::all();
        $param['user_id'] = $user['id'];
//        \Log::info('Reject Challenge : ' . json_encode(\Input::all()));
        
        $validator = \Validator::make(\Input::all(), \Validation::get_rules("game", "gameid"));
        
        if($validator->fails()) {
            $messages = $validator->messages();
            $error = $messages->all();
            $json = \General::validation_error_res();
            $json['data'] = $error;
            $json['msg'] = "Please Fill Requeried filled.";
//            \Log::info('Join Friend Response : ' . json_encode($json));
            return \Response::json($json, 200);
        }
        

            $ch=\App\Models\MultiPlayerChallenge::with('user')->where('id',$param['challenge_id'])->first();
            if(is_null($ch)){
                  $json = \General::error_res('Challenger Id not Found');
                  return \Response::json($json, 200);
            }
            
            $send_user=\App\Models\Users::select('id','device_type','device_token')->active()->where('email',$ch->user->email)->first()->toArray();
//            $d_tokens=\General::get_field_array($user_list,'device_token');            
          
            $av_path='';
            if($user['avatar']==''){
                $av_path=\URL::to("assets/images/my_avatar.jpg");
            }else{
                $av_path=config('constant.USER_AVATAR_PATH_LINK').'/'.$user['avatar'];
            }
            
            
            $meta = array();
            $device_tokens = array($send_user['device_token']);
            $msg = array($user['username']." rejected your challenge");
            $title = array("Challenge Rejected");
            $badge = array(1);
            $screen = array($param['challenge_id']);
            $meta = array(2);
            $img = array($av_path);

            $res = \App\Lib\FCM::send_notification_with_data($device_tokens,$msg,$send_user['device_type'],$title);
            // if($send_user['device_type']==config('constant.ANDROID_APP_DEVICE')){
            //     $resAndroidPush = \App\Lib\Push::android_push($device_tokens,$msg,$title,$badge,$screen,$meta,[],$img);
            // }elseif($send_user['device_type']==config('constant.IPHONE_APP_DEVICE')){
            //     $resApplePush = \App\Lib\Push::iphone_push($device_tokens,$msg,$title,$badge,$screen,$meta,[],$img);
            // }
            
            $nParam['user_id']=$send_user['id'];
            $nParam['notify_msg']=$user['username']." rejected your challenge";
            $nParam['type']=2;
            $nParam['challenge_id']=$param['challenge_id'];
            $nParam['responder_user_id']=$user['id'];
            $resNotify=\App\Models\Notifications::add_notification($nParam);
 
           $resNot=\App\Models\Notifications::where('user_id',$user['id'])->where('challenge_id',$param['challenge_id'])->where('type',0)->first();
            if(!is_null($resNot)){
                $resNot->delete();
            } 
            
     
        $res=\General::success_res('Reject challenge Notification Sent Successfully');
//        \Log::info('Reject Challenge Response:'.json_encode($res));
        return \Response::json($res, 200);
        
    }
    
//    public static  function randomMatchNumber(){
//         $settings=app('settings');
//         $mrange=  explode('-', $settings['match_num_range']);
//         return mt_rand($mrange[0],$mrange[1]);
//    }
    
    public static  function randomHint($type){
         $settings=app('settings');
         $user = app("logged_in_user");
        
         if($type=="multi"){
             $levelID= \App\Models\MultiPlayerStatics::where('user_id',$user['id'])->value('current_game_level');
         }elseif($type=="single"){
             $levelID= \App\Models\SinglePlayerStatics::where('user_id',$user['id'])->value('current_game_level');
         }
         
         $level=  \App\Models\GameLevelRules::where('level',$levelID)->first();
         $data=array();
         if(is_null($level)){
             $res= \General::error_res('Opps ! Something might wrong');
         }
         else{
             $res['flag']=1;
             $res['hint']= mt_rand(0, $level->hint_range);
         }
         
         return $res;
    }
    
    public static  function randomMatchNumber($type,$challnger_id=null){
        $settings=app('settings');
        $user = app("logged_in_user");
        if($type=="multi"){
            if($challnger_id){
                $tplayer=\App\Models\MultiPlayerChallengePlayer::where('id',$challnger_id)->first();
                $levelID= \App\Models\MultiPlayerChallengePlayer::where('challenge_id',$tplayer->challenge_id)->max('current_game_level');
            }else{
               $levelID= \App\Models\MultiPlayerStatics::where('user_id',$user['id'])->value('current_game_level');
            }
        }elseif($type=="single"){
            $levelID= \App\Models\SinglePlayerStatics::where('user_id',$user['id'])->value('current_game_level');
        }
         
         $level=  \App\Models\GameLevelRules::where('level',$levelID)->first();
         $data=array();
         if(is_null($level)){
             $res= \General::error_res('Opps ! Something might wrong');
         }
         else{
             $lastStat =  \App\Models\MultiPlayerStatics::where('user_id',$user['id'])->first();
             $res['flag']=1;
             $res['match_num']=  mt_rand($level->min_match_num, $level->max_match_num);
             $res['level'] =  [
                 'level'=> isset($level->level) ? $level->level : 0,
                 'name'=>  isset($level->name) ? $level->name : '',
                 'slogan'=>  isset($level->slogan) ? $level->slogan : '',
                 'avatar'=>  isset($level->avatar) ? $level->avatar : '',
             ] ;
             for($i=0;$i<$settings['max_hint_per_game'];$i++){
                 $res['hint'][$i]= mt_rand(0, $level->hint_range);
             }
         }
         \Log::info("Level Object :".  json_encode($res));
         return $res;
    }
    
    
    public static function checkResultTime($id=''){
       
        $chPlayer=\App\Models\MultiPlayerChallengePlayer::where('status','1')->where('challenge_id',$id)->first();
        
        if(is_null($chPlayer)){
           return true;
        }else{
          return  false;
        }
        
    }
    
    public static function declareResult($id=''){
       
       $settings=app('settings');
       $curTime=date('H:i');
       $curDate=date('Y-m-d');
       $curDateTime=date('Y-m-d H:i');
   
        
        $resCng=  \App\Models\MultiPlayerChallenge::active()->where('id',$id)->get()->toArray();
        
        if(count($resCng)<1){
            return \General::success_res('No Multi Player Game Found For Result Declaration');
        }
        
        foreach($resCng as $rc){

           $cPlayer=\App\Models\MultiPlayerChallengePlayer::select('id',DB::raw('max(score) as mscore'))->where('challenge_id',$rc['id'])->first();  

           
           if(!is_null($cPlayer)){
               $cPlayer=$cPlayer->toArray();
               $allPlayer=\App\Models\MultiPlayerChallengePlayer::where('challenge_id',$rc['id'])->where('score','=',$cPlayer['mscore'])->get()->toArray();             

           }else{
               continue;
           }

           $allId=\General::get_field_array($allPlayer,'id');

           $wonid='';
           if(count($allPlayer)>1){
                $uids=array();
               foreach($allPlayer as $ap){
                   
                   $mpUser=\App\Models\MultiPlayerStatics::where('user_id',$ap['user_id'])->first();
                   
                   $mParam['user_id']=$mpUser->user_id;
                   $mParam['played']=$mpUser->played+1;
                   $mParam['won']=$mpUser->won+0;
                   $mParam['tie']=$mpUser->tie+1;
                   $mParam['lost']=$mpUser->lost+0;
                   $mParam['score']=$mpUser->total_score+$ap['score'];
                   
                   $resMS=\App\Models\MultiPlayerStatics::update_multi_player_statics($mParam);
                   $resMP=\App\Models\MultiPlayerChallengePlayer::where('id',$ap['id'])->update(['won_status'=>2]);

                   array_push($uids, $mpUser->user_id);
                   
               }
               
                   $user_list=\App\Models\Users::select('id','device_type','device_token')->active()->whereIn('id',$uids)->get()->toArray();
                   
                   foreach ($user_list as $send_user){
                        $meta = array();
                        $device_tokens = array($send_user['device_token']);
                        $msg = array("Match result is Tie");
                        $title = array("MATcHUP Challenge Result is Declared");
                        $badge = array(1);
                        $screen = array($rc['id']);
                        $meta = array(5);
                        $img = array();
                        $res = \App\Lib\FCM::send_notification_with_data($device_tokens,$msg,$send_user['device_type'],$title);
                        // if($send_user['device_type']==config('constant.ANDROID_APP_DEVICE')){
                        //     $res = \App\Lib\Push::android_push($device_tokens,$msg,$title,$badge,$screen,$meta,[],$img);
                        // }elseif($send_user['device_type']==config('constant.IPHONE_APP_DEVICE')){
                        //     $res = \App\Lib\Push::iphone_push($device_tokens,$msg,$title,$badge,$screen,$meta,[],$img);
                        // }
                        
                        $nParam=[];
                        $nParam['user_id']=$send_user['id'];
                        $nParam['notify_msg']="Challenge result is declared ,Result is : Tie";
                        $nParam['type']=5;
                        $nParam['challenge_id']=$rc['id'];
                        $resNotify=\App\Models\Notifications::add_notification($nParam);
                   }
                   
                $wonid='';
           }else{
                 
                   $mpUser=  \App\Models\MultiPlayerStatics::where('user_id',$allPlayer[0]['user_id'])->first();
//                   dd($mpUser);
                   $mParam['user_id']=$mpUser->user_id;
                   $mParam['played']=$mpUser->played+1;
                   $mParam['won']=$mpUser->won+1;
                   $mParam['tie']=$mpUser->tie+0;
                   $mParam['lost']=$mpUser->lost+0;
                   $mParam['score']=$mpUser->total_score+$allPlayer[0]['score'];
                   
                   $resMS=\App\Models\MultiPlayerStatics::update_multi_player_statics($mParam);
                   
                   
                    if($resMS['flag']==1){
    
                    }
                   

                   $resMP=\App\Models\MultiPlayerChallengePlayer::where('id',$allPlayer[0]['id'])->update(['won_status'=>1]);
                   $wonid=$mpUser->user_id;

                   $user_list=\App\Models\Users::select('id','device_token','device_token','device_type')->active()->where('id',$mpUser->user_id)->get()->toArray();
                  
                   foreach ($user_list as $send_user){
                        $meta = array();
                        $device_tokens = array($send_user['device_token']);
                        $msg = array("You won the match");
                        $title = array("MATcHUP Challenge  Result is Declared");
                        $badge = array(1);
                        $screen = array($rc['id']);
                        $meta = array(3);
                        $img = array();

                        $res = \App\Lib\FCM::send_notification_with_data($device_tokens,$msg,$send_user['device_type'],$title);
                        // if($send_user['device_type']==config('constant.ANDROID_APP_DEVICE')){
                        //     $res = \App\Lib\Push::android_push($device_tokens,$msg,$title,$badge,$screen,$meta,[],$img);
                        // }elseif($send_user['device_type']==config('constant.IPHONE_APP_DEVICE')){
                        //     $res = \App\Lib\Push::iphone_push($device_tokens,$msg,$title,$badge,$screen,$meta,[],$img);
                        // }

                        $nParam=[];
                        $nParam['user_id']=$send_user['id'];
                        $nParam['notify_msg']="Challenge result is declared ,You Won challenge";
                        $nParam['type']=3;
                        $nParam['challenge_id']=$rc['id'];
                        $resNotify=\App\Models\Notifications::add_notification($nParam);
                   }
           }
           
         
           $allLostPlayer=\App\Models\MultiPlayerChallengePlayer::where('challenge_id',$rc['id'])->whereNotIn('id',$allId)->get()->toArray();  
           $uids=array();
           foreach($allLostPlayer as $ap){
                   
                   $mpUser=  \App\Models\MultiPlayerStatics::where('user_id',$ap['user_id'])->first();
                  
                   $mParam['user_id']=$mpUser->user_id;
                   $mParam['played']=$mpUser->played+1;
                   $mParam['won']=$mpUser->won+0;
                   $mParam['tie']=$mpUser->tie+0;
                   $mParam['lost']=$mpUser->lost+1;
                   $mParam['score']=$mpUser->total_score+$ap['score'];
                   
                   $resMS=\App\Models\MultiPlayerStatics::update_multi_player_statics($mParam);
                   $resMP=\App\Models\MultiPlayerChallengePlayer::where('id',$ap['id'])->update(['won_status'=>0]);
                   
                   array_push($uids, $mpUser->user_id);

           }
           
           
                    $user_list=\App\Models\Users::select('id','device_type','device_token')->active()->whereIn('id',$uids)->get()->toArray();
                    
                    foreach ($user_list as $send_user){
                        $meta = array();
                        $device_tokens = array($send_user['device_token']);
                        $msg = array("You Lost the match");
                        $title = array("MATcHUP Challenge  Result is Declared");
                        $badge = array(1);
                        $screen = array($rc['id']);
                        $meta = array(4);
                        $img = array();

                        $res = \App\Lib\FCM::send_notification_with_data($device_tokens,$msg,$send_user['device_type'],$title);
                        // if($send_user['device_type']==config('constant.ANDROID_APP_DEVICE')){
                        //     $res = \App\Lib\Push::android_push($device_tokens,$msg,$title,$badge,$screen,$meta,[],$img);
                        // }elseif($send_user['device_type']==config('constant.IPHONE_APP_DEVICE')){
                        //     $res = \App\Lib\Push::iphone_push($device_tokens,$msg,$title,$badge,$screen,$meta,[],$img);
                        // }

                        $nParam=[];
                        $nParam['user_id']=$send_user['id'];
                        $nParam['notify_msg']="Challenge result is declared ,Result is : Lost";
                        $nParam['type']=4;
                        $nParam['challenge_id']=$rc['id'];
                        $resNotify=\App\Models\Notifications::add_notification($nParam);

                    }
           
           $wParam['id']=$rc['id'];
           $wParam['won_id']=$wonid;
           $resMpc=\App\Models\MultiPlayerChallenge::update_won_challenge($wParam);
        }

        return \General::success_res('Winner Successfully Declared');
        
     }

    public function postAddChallengePlayerGame(){
        
        $user = app("logged_in_user");
        $param = \Input::all();
        $param['user_id'] = $user['id'];
        $settings= app('settings');
        \Log::info('Add Challenge Player Game : '.json_encode(\Input::all()));
        
        $validator = \Validator::make(\Input::all(), \Validation::get_rules("game", "add_challenge_game"));
        
        if($validator->fails()) {
            $messages = $validator->messages();
            $error = $messages->all();
            $json = \General::validation_error_res();
            $json['data'] = $error;
            $json['msg'] = "Please Fill Requeried filled.";
//            \Log::info('Add Challenge Player Game : ' . json_encode($json));
            return \Response::json($json, 200);
        }
        
        $tplayed=\App\Models\MultiPlayerChallengePlayer::where('id',$param['challenge_player_id'])->first();

        if($tplayed->game_played==$settings['total_game_per_challenge']){
             $json = \General::error_res('You Had Played Your all Games,wait for result');
             return \Response::json($json, 200);
        }
        
         $resRange=self::randomMatchNumber('multi',$param['challenge_player_id']);
         $param=  array_merge($param,$resRange);
         if($resRange['flag']!=1){
                return \Response::json($param, 200);
         }
        
        $res=\App\Models\MultiPlayerChallengePlayerGame::add_challenge_player_game($param);
        $res['data']['match_num']=$param['match_num'];
        $res['data']['hint_num']=$param['hint'];
        $res['data']['level']=$param['level'];
        $res['data']['total_played']=$tplayed->game_played;
//        \Log::info('Add Challenge Player Game :');
        return \Response::json($res, 200);
        
    }

    public function postSkipChallengePlayerGame(){
        
        $user = app("logged_in_user");
        $param = \Input::all();
        $param['user_id'] = $user['id'];
          $settings= app('settings');
//        \Log::info('Skip Challenge Player Game : '.json_encode(\Input::all()));
        
        $validator = \Validator::make(\Input::all(), \Validation::get_rules("game", "skip_challenge_game"));
        
        if($validator->fails()) {
            $messages = $validator->messages();
            $error = $messages->all();
            $json = \General::validation_error_res();
            $json['data'] = $error;
            $json['msg'] = "Please Fill Requeried filled.";
//            \Log::info('Skip Challenge Player Game : ' . json_encode($json));
            return \Response::json($json, 200);
        }
        
        $res=\App\Models\MultiPlayerChallengePlayerGame::skip_challenge_player_game($param);
        
        if($res['flag']==1){
            
            
            $resChallenge=\App\Models\MultiPlayerChallengePlayer::where('id',$param['challenge_player_id'])->first();
            if(is_null($resChallenge)){
                 $json = \General::error_res('Challenge Player Game Id not Found');
                 return \Response::json($json, 200);
            }
            
            $resDeclare=self::checkResultTime($resChallenge->challenge_id);
            
            if($resDeclare){
               
                $resResult=self::declareResult($resChallenge->challenge_id);
            
                $wonStatus = \App\Models\MultiPlayerChallengePlayer::where('id',$param['challenge_player_id'])->first();
                $levelID= \App\Models\MultiPlayerChallengePlayer::where('id',$param['challenge_player_id'])->max('current_game_level');
                $level=  \App\Models\GameLevelRules::where('level',$levelID)->first();

                if($wonStatus->won_status!=4){
                    $json = \General::info_res('');
                    $json['data']['won_status']=$wonStatus->won_status;
                    $json['data']['score']=$wonStatus->score;
                    $json['data']['level'] =  [
                        'level'=> isset($level->level) ? $level->level : 0,
                        'name'=>  isset($level->name) ? $level->name : '',
                        'slogan'=>  isset($level->slogan) ? $level->slogan : '',
                        'avatar'=>  isset($level->avatar) ? $level->avatar : '',
                    ] ;
                    return \Response::json($json, 200);
                }
            }


            
            $tplayed=\App\Models\MultiPlayerChallengePlayer::where('id',$param['challenge_player_id'])->first();

            if($tplayed->game_played==$settings['total_game_per_challenge']){
                 $json = \General::error_res('You Had Played Your all Games,wait for result');
                 // $json['data']['won_status']=$tplayed->won_status;
                 return \Response::json($json, 200);
            }
            
             
               $resRange=self::randomMatchNumber('multi',$param['challenge_player_id']);
               $param=  array_merge($param,$resRange);
                if($resRange['flag']!=1){
                    return \Response::json($param, 200);
                }
               $res=\App\Models\MultiPlayerChallengePlayerGame::add_challenge_player_game($param);
               $res['data']['match_num']=$param['match_num'];
               $res['data']['hint_num']=$param['hint'];
               $res['data']['level']=$param['level'];
               $res['data']['total_played']=$tplayed->game_played;
        }
       
//        \Log::info('Skip Challenge Player Game :'.json_encode($res));
        return \Response::json($res, 200);
        
    }

    public function postUpdateChallengePlayerGame(){
        
        $user = app("logged_in_user");
        $param = \Input::all();
        $param['user_id'] = $user['id'];
        $settings= app('settings');
        \Log::info('Update Challenge Player Game : '.json_encode(\Input::all()));
        
        $validator = \Validator::make(\Input::all(), \Validation::get_rules("game", "update_challenge_game"));
        
        if($validator->fails()) {
            $messages = $validator->messages();
            $error = $messages->all();
            $json = \General::validation_error_res();
            $json['data'] = $error;
            $json['msg'] = "Please Fill Requeried filled.";
//            \Log::info('Update Challenge Player Game : ' . json_encode($json));
            return \Response::json($json, 200);
        }
        
        $cPlayer=  \App\Models\MultiPlayerChallengePlayer::where('id',$param['challenge_player_id'])->first();
        if(is_null($cPlayer)){
                 $res = \General::error_res('Challenge Player Id Not found');
                 return \Response::json($res, 200);
        }
        
        $timedStatus=\App\Models\MultiPlayerChallenge::where('id',$cPlayer->challenge_id)->first();
        
        if(is_null($timedStatus)){
            
                 $res = \General::error_res('Challenge  Not found');
                 return \Response::json($res, 200);
                 
        }else{
            if($timedStatus->type==1){
                
                $timed_rules=[
                   'time'=>'required|numeric',
                   'timed_score'=>'required|numeric'
                    
                ];
                $validator = \Validator::make(\Input::all(),$timed_rules);
        
                if($validator->fails()) {
                    $messages = $validator->messages();
                    $error = $messages->all();
                    $json = \General::validation_error_res();
                    $json['data'] = $error;
                    $json['msg'] = "In TimeMaster Game Additional parameter are required.";
//                    \Log::info('Update Challenge Player Game : ' . json_encode($json));
                    return \Response::json($json, 200);
                }
           
                $param['time_check']='yes';
               
            }
        }
        
        
        $equ=$param['equation'];

        
        try {
            $parser = new FormulaParser($equ,1);
            $result = $parser->getResult();
            $equResult=(int)$result[1];
//           dd($equResult);
        } catch (\Exception $e) {
//            echo $e->getMessage();
                $res = \General::error_res($e->getMessage());
                return \Response::json($res, 200);
        }
         
        
        $resChallenge=\App\Models\MultiPlayerChallengePlayerGame::where('id',$param['challenge_game_id'])->first();
        if(is_null($resChallenge)){
                 $res = \General::error_res('Challenge game Id Not found');
                 return \Response::json($res, 200);
        }
        
        if($resChallenge->match_num!=$equResult){
               $param['won_status']=0;
        }else{
            $param['won_status']=1;
        }
        
        
        
        preg_match_all('!\d+!', $equ, $matches);
        $mCnt=count($matches[0]);
      
        $sc=100;
        $finalScore=0;
        
        
        if($timedStatus->type==0){
            if($param['won_status']==1){
                if($param['hint']<=2){
                    if($mCnt<=4){
                        $finalScore=100;
                    }else{ 
                        $finalScore=$sc-(($mCnt-4)*$settings['per_hint_reduce']);
                    }
                }else{
                    switch ($param['hint']){
                        case 1:
                        case 2:$sc=100;
                            break;
                        case 3:$sc=90;
                            break;
                        case 4:$sc=80;
                            break;

                    }
                    if($mCnt<=4){
                        $finalScore=$sc;
                    }else{
                        $finalScore=$sc-(($mCnt-4)*$settings['per_hint_reduce']);
                    }
                    
                }
            }
                
        }elseif ($timedStatus->type==1){
            
            $finalScore=$param['timed_score'];
        }
        
        
        
        $param['score']=$finalScore;

        $res=\App\Models\MultiPlayerChallengePlayerGame::update_challenge_player_game($param);
   
        if($res['flag']==1){
            \App\Models\MultiPlayerChallengePlayer::updateTemporaryPlayerLevel($param['challenge_player_id']);
            $resChallenge=\App\Models\MultiPlayerChallengePlayer::where('id',$param['challenge_player_id'])->first();
            if(is_null($resChallenge)){
                 $json = \General::error_res('Challenge Player Game Id not Found');
                 return \Response::json($json, 200);
            }
            
            $resDeclare=self::checkResultTime($resChallenge->challenge_id);
            
            if($resDeclare){
                
                
                $resResult=self::declareResult($resChallenge->challenge_id);
                
                $resStatus=self::updatePlayerLevel($user,'multi');
                if($resStatus['flag']!=1){
                    $res = \General::error_res('Opps ! something might wrong.');
                    return \Response::json($res, 200);
                }
                
                
                
                $wonStatus=\App\Models\MultiPlayerChallengePlayer::where('id',$param['challenge_player_id'])->first();    
                    if($wonStatus->won_status!=4){
                        $json = \General::info_res('');
                        $json['data']['won_status']=$wonStatus->won_status;
                        $json['data']['score']=$wonStatus->score;
                        return \Response::json($json, 200);
                    }
                    
                    
            }
            
            
            $tplayed=\App\Models\MultiPlayerChallengePlayer::where('id',$param['challenge_player_id'])->first();
            
            if($tplayed->game_played==$settings['total_game_per_challenge']){
                 
                $resStatus=self::updatePlayerLevel($user,'multi');
                if($resStatus['flag']!=1){
                    $res = \General::error_res('Opps ! something might wrong.');
                    return \Response::json($res, 200);
                }
                
                
                 $json = \General::error_res('You Had Played Your all Games,wait for result');
                 // $json['data']['won_status']=$tplayed->won_status;
                 return \Response::json($json, 200);
                 
            }
            
             
               $resRange=self::randomMatchNumber('multi',$param['challenge_player_id']);
               $param=  array_merge($param,$resRange);
                if($resRange['flag']!=1){
                    return \Response::json($param, 200);
                }
               $res=\App\Models\MultiPlayerChallengePlayerGame::add_challenge_player_game($param);
               $res['data']['match_num']=$param['match_num'];
               $res['data']['hint_num']=$param['hint'];
               $res['data']['level']=$param['level'];
               $res['data']['total_played']=$tplayed->game_played;
        }
        
        
        \Log::info('Update Challenge Player Game :');
        \Log::info(json_encode($res));
        return \Response::json($res, 200);
        
    }
    
    public function postAddSinglePlayerGame(){
        
        $user = app("logged_in_user");
        $param = \Input::all();
        $param['user_id'] = $user['id'];
        $settings= app('settings');
        
//        \Log::info('Add Single Player Game : '.json_encode(\Input::all()));
        
        $validator = \Validator::make(\Input::all(), \Validation::get_rules("game", "add_single_game"));
        
        if($validator->fails()) {
            $messages = $validator->messages();
            $error = $messages->all();
            $json = \General::validation_error_res();
            $json['data'] = $error;
            $json['msg'] = "Please Fill Requeried filled.";
//            \Log::info('Add Single Player Game : ' . json_encode($json));
            return \Response::json($json, 200);
        }
        
            $resRange=self::randomMatchNumber('single');
            $param=  array_merge($param,$resRange);
            
            if($resRange['flag']!=1){
                return \Response::json($param, 200);
            }
            
             $res=\App\Models\SinglePlayerGame::add_single_player_game($param);
           
             $res['data']['match_num']=$param['match_num'];
             $res['data']['hint_num']=$param['hint'];
             $res['data']['level']=$param['level'];

//        \Log::info('Add Single Player Game :');
        return \Response::json($res, 200);
        
    }
    
    public function postSkipSinglePlayerGame(){
        
        $user = app("logged_in_user");
        $param = \Input::all();
        $param['user_id'] = $user['id'];
          $settings= app('settings');
//        \Log::info('Skip Single Player Game : '.json_encode(\Input::all()));
        
        $validator = \Validator::make(\Input::all(), \Validation::get_rules("game", "skip_single_game"));
        
        if($validator->fails()) {
            $messages = $validator->messages();
            $error = $messages->all();
            $json = \General::validation_error_res();
            $json['data'] = $error;
            $json['msg'] = "Please Fill Requeried filled.";
            \Log::info('Skip Single Player Game : ' .json_encode($json));
            return \Response::json($json, 200);
        }
        
        $res=\App\Models\SinglePlayerGame::skip_single_player_game($param);
        $resStat=  \App\Models\SinglePlayerStatics::where('user_id',$user['id'])->first();
        $level=  \App\Models\GameLevelRules::where('level',$resStat['current_game_level'])->first();

        $res['level'] =  [
            'level'=> isset($level->level) ? $level->level : 0,
            'name'=>  isset($level->name) ? $level->name : '',
            'slogan'=>  isset($level->slogan) ? $level->slogan : '',
            'avatar'=>  isset($level->avatar) ? $level->avatar : '',
        ] ;
//        if($res['flag']==1){
//               $res=\App\Models\SinglePlayerGame::add_single_player_game($param);
//        }
       
        \Log::info('Skip Single Player Game :'.json_encode($res));
        return \Response::json($res, 200);
        
    }
    
    public function postUpdateSinglePlayerGame(){
        
        $user = app("logged_in_user");
        $param = \Input::all();
        $param['user_id'] = $user['id'];
        $settings= app('settings');
//        \Log::info('Update Single Player Game : '.json_encode(\Input::all()));
        
        $validator = \Validator::make(\Input::all(), \Validation::get_rules("game", "update_single_game"));
        
        if($validator->fails()) {
            $messages = $validator->messages();
            $error = $messages->all();
            $json = \General::validation_error_res();
            $json['data'] = $error;
            $json['msg'] ="Please Fill Requeried filled.";
//            \Log::info('Update Single Player Game : ' . json_encode($json));
            return \Response::json($json, 200);
        }
        
        $sgame=  \App\Models\SinglePlayerGame::active()->where('id',$param['game_id'])->first();
        
        if(is_null($sgame)){
            $res = \General::error_res('Game Expire or Id Not found');
            return \Response::json($res, 200);
        }

        if($sgame->type==1){
           
             
                $timed_rules=[
                   'time'=>'required|numeric',
                   'timed_score'=>'required|numeric'
                    
                ];
                $validator = \Validator::make(\Input::all(),$timed_rules);
        
                if($validator->fails()) {
                    $messages = $validator->messages();
                    $error = $messages->all();
                    $json = \General::validation_error_res();
                    $json['data'] = $error;
                    $json['msg'] = "In TimeMaster Game Additional paraneter are required.";
//                    \Log::info('Update Challenge Player Game : ' . json_encode($json));
                    return \Response::json($json, 200);
                }
           
                $param['time_check']='yes';
        }
        
        
        
        $equ=$param['equation'];

        
        try {
            $parser = new FormulaParser($equ,1);
            $result = $parser->getResult();
            $equResult=(int)$result[1];
//           dd($equResult);
        } catch (\Exception $e) {
//            echo $e->getMessage();
                $res = \General::error_res($e->getMessage());
                return \Response::json($res, 200);
        }
         
        
        if($sgame->match_num!=$equResult){
               $param['won_status']=0;
        }else{
            $param['won_status']=1;
        }
        
        
        
        preg_match_all('!\d+!', $equ, $matches);
        $mCnt=count($matches[0]);
      
        $sc=100;
        $finalScore=0;
        
     
        if($sgame->type==0){
            if($param['won_status']==1){
                if($param['hint']<=2){
                    if($mCnt<=4){
                        $finalScore=100;
                    }else{
                        $finalScore=$sc-(($mCnt-4)*$settings['per_hint_reduce']);
                    }
                     
                }else{
                    switch ($param['hint']){
                        case 1:
                        case 2:$sc=100;
                            break;
                        case 3:$sc=90;
                            break;
                        case 4:$sc=80;
                            break;

                    }
                       
                    if($mCnt<=4){
                        $finalScore = $sc;
                    }else{
                        $finalScore = $sc-(($mCnt-4)*$settings['per_hint_reduce']);
                    }
                }
            }
                
        }elseif ($sgame->type==1){
           
            $finalScore=$param['timed_score'];
        }
     
        $param['score']=$finalScore;

        $res=\App\Models\SinglePlayerGame::update_single_player_game($param);
        
        if($res['flag']==1){
           
            $resStat=  \App\Models\SinglePlayerStatics::where('user_id',$user['id'])->first();
            if(!is_null($resStat)){ 
                $resStatus=self::updatePlayerLevel($user,'single',$finalScore);
                $level = \App\Models\GameLevelRules::where('level',$resStat['current_game_level'])->first();
                $res['level']=[
                    'level'=> isset($level->level) ? $level->level : 0,
                    'name'=>  isset($level->name) ? $level->name : '',
                    'slogan'=>  isset($level->slogan) ? $level->slogan : '',
                    'avatar'=>  isset($level->avatar) ? $level->avatar : '',
                ];
                if($resStatus['flag']==1){
                    $res['can_retry']=1;//$resStatus['can_retry'];
                    //if($resStatus['can_retry']==1){
                        $sgame->can_retry = 1;
                        $sgame->save();
                    //}
                }else{
                    $res = \General::error_res('Opps ! something might wrong.');
                    return \Response::json($res, 200);
                }
            }
        }
//        \Log::info('Update Single Player Game :');
        return \Response::json($res, 200);
        
    }
    public function postRetry(){
        $json =  \General::error_res('Opps ! Something might wrong');
        
        $user = app("logged_in_user");
        $param = \Input::all();
        $param['user_id'] = $user['id'];
        $settings= app('settings');
        $last_game = \App\Models\SinglePlayerGame::where('player_id',$user['id'])->orderBy('id','desc')->first();
        
        
        if($last_game->can_retry==1){
            $validator = \Validator::make(\Input::all(), \Validation::get_rules("game", "add_single_game"));
            
            if($validator->fails()) {
                $messages = $validator->messages();
                $error = $messages->all();
                $json = \General::validation_error_res();
                $json['data'] = $error;
                $json['msg'] = "Please Fill Requeried filled.";
            }else{
                $resRange=self::randomMatchNumber('single');
                $param=  array_merge($param,$resRange);

                if($resRange['flag']!=1){
                    return \Response::json($param, 200);
                }

                $res=\App\Models\SinglePlayerGame::add_single_player_game($param);
                $res['data']['match_num']=$param['match_num'];
                $res['data']['hint_num']=$param['hint'];
                $res['data']['level']=$param['level'];
                $json = $res;
            }
        }
        return \Response::json($json, 200);
    }    
    public function postGameStatics(){
        
        $user = app("logged_in_user");
        $param = \Input::all();
        $param['user_id'] = $user['id'];
        $settings= app('settings');
        
//        \Log::info('Game Statics   : '.json_encode(\Input::all()));
        
       
        $resSingle=\App\Models\SinglePlayerStatics::select('total_played','classic_played','timed_played','avg_score','avg_score_classic','avg_score_timed','high_score','high_score_classic','high_score_timed')->where('user_id',$param['user_id'])->first();
       
        if(is_null($resSingle)){
            $resSingle=[];
        }
        
        $resMulti=\App\Models\MultiPlayerStatics::select('played','won','tie','lost','total_score')->where('user_id',$param['user_id'])->first();
        
        if(is_null($resMulti)){
           $resMulti=[];
        }
      
        $res=\General::success_res('Player All Game Statics');
        $res['data']['single_player']=$resSingle;
        $res['data']['multi_player']=$resMulti;
//        \Log::info('Game Statics res :'.  json_encode($res));
        return \Response::json($res, 200);
        
    }
    
    public function postMpLeaderboardStatics(){
        
        $user = app("logged_in_user");
        $param = \Input::all();
        $param['user_id'] = $user['id'];
        $settings= app('settings');
        
//        \Log::info('MultiPlayer Leaderboard Statics   : '.json_encode(\Input::all()));
        


        $resLB=\App\Models\MultiPlayerStatics::select('user_id','total_score')->with(['user'=>function($query){
                $query->select('id','username','avatar');
            }])->orderBy('total_score','desc')->take(10)->get()->toArray();
        

        $res=\General::success_res('MultiPlayer Leaderboard Statics');
   
        $res['data']['leaderboard']=$resLB;
//        \Log::info('MultiPlayer Leaderboard Statics res :'.  json_encode($res));
        return \Response::json($res, 200);
        
    }
    
    public function postSpLeaderboardStatics(){
        
        $user = app("logged_in_user");
        $param = \Input::all();
        $param['user_id'] = $user['id'];
        $settings= app('settings');
        
//        \Log::info('Single PLayer Leaderboard Statics   : '.json_encode(\Input::all()));
        

        $resLB=\App\Models\SinglePlayerStatics::select('user_id','high_score')->with(['user'=>function($query){
            $query->select('id','username','avatar');
        }])->orderBy('avg_score','desc')->take(10)->get()->toArray();
        

        $res=\General::success_res('Single Player Leaderboard Statics');
   
        $res['data']['leaderboard']=$resLB;
//        \Log::info('SinglePLayer Leaderboard Statics :'.  json_encode($res));
        return \Response::json($res, 200);
        
    }

    public function postUserOngoingBoard(){
        
        $user = app("logged_in_user");
        $param = \Input::all();
        $param['user_id'] = $user['id'];
        $settings= app('settings');
//        \Log::info('User On giong board : '.json_encode(\Input::all()));
        

        $tplayed=\App\Models\MultiPlayerChallengePlayer::select('id','user_id','challenge_id','game_played')->active()->whereHas('challenge', function ($query) {
              $query->where('status','=',1)->where('friend_invite_status','=',1);})
          ->with(['challenge'=>function($query){
            $query->select('id','type','challenger_user_id','challenger_msg','total_player','status','end_time');
          },'challenge.user'=>function($query){
            $query->select('id','avatar');
          }])->where('user_id',$param['user_id'])->orderBy('id','desc')->get()->toArray();
        
        $res=\General::success_res('Ongoing board Game List');
        $res['data']=$tplayed;
//        \Log::info('User On giong board :');
        return \Response::json($res, 200);
        
    }
    
    public function postChallengeInfo(){
        
        $user = app("logged_in_user");
        $param = \Input::all();
        $param['user_id'] = $user['id'];
        $settings= app('settings');
//        \Log::info('Challenge Info : '.json_encode(\Input::all()));
        
        $validator = \Validator::make(\Input::all(), \Validation::get_rules("game", "challenge_info"));
        
        if($validator->fails()) {
            $messages = $validator->messages();
            $error = $messages->all();
            $json = \General::validation_error_res();
            $json['data'] = $error;
            $json['msg'] ="Please Fill Requeried filled.";
            \Log::info('Challenge Info : ' . json_encode($json));
            return \Response::json($json, 200);
        }
        
        $sgame= \App\Models\MultiPlayerChallenge::select('id','type')->active()->where('id',$param['challenge_id'])->first();
        
        if(is_null($sgame)){
            $res = \General::error_res('Challenge Info Not Found');
            return \Response::json($res, 200);
        }
 
        $res= \General::success_res('Challenge Info');
        $res['data']=$sgame->toArray();
//        \Log::info('Challenge Info:');
        return \Response::json($res, 200);
        
    }
    
    public function postGetHint(){
        
        $user = app("logged_in_user");
        $param = \Input::all();
        $param['user_id'] = $user['id'];
        $settings= app('settings');
//        \Log::info('Get Hint : '.json_encode(\Input::all()));
        
        
        if(isset($param['challenge_game_id']) && isset($param['game_id'])){
            $json = \General::error_res('Request at a time Only One Paramaeter either challenge_game_id or game_id');
            return \Response::json($json, 200);
        }
        
        
        $validator = \Validator::make(\Input::all(), \Validation::get_rules("game", "add_hint"));
        
        if($validator->fails()) {
            $error = $validator->messages()->all();
            $json = \General::validation_error_res("Please Fill Requeried filled.");
            $json['data'] = $error;
//            \Log::info('Add Challenge Player Game : ' . json_encode($json));
            return \Response::json($json, 200);
        }
        
        if(isset($param['challenge_game_id'])){
            $resMulti=  \App\Models\MultiPlayerChallengePlayerGame::where('id',$param['challenge_game_id'])->first();
            if(is_null($resMulti)){
                $json = \General::error_res('Challenge Game Id Not Found');
                return \Response::json($json, 200);
            }
            $resRange=self::randomHint('multi');
        }
        
        if(isset($param['game_id'])){
            $resSingle=\App\Models\SinglePlayerGame::where('id',$param['game_id'])->first();
            if(is_null($resSingle)){
                $json = \General::error_res('Game Id Not Found');
                return \Response::json($json, 200);
            }
            $resRange=self::randomHint('single');
        }

         if($resRange['flag']!=1){
                return \Response::json($resRange, 200);
         }
        
        $res=\General::success_res('New Hint Number');
        $res['data']['hint_num']=$resRange['hint'];
    
        \Log::info('Get Hint  :');
        return \Response::json($res, 200);
    }
    
    public function postChallengeScore(){
        
        $user = app("logged_in_user");
        $param = \Input::all();
        $param['user_id'] = $user['id'];
        $settings= app('settings');
        \Log::info('Challenge Score : '.json_encode(\Input::all()));
        
        $validator = \Validator::make(\Input::all(), \Validation::get_rules("game", "challenge_score"));
        
        if($validator->fails()) {
            $error = $validator->messages()->all();
            $json = \General::validation_error_res("Please Fill Requeried filled.");
            $json['data'] = $error;
            \Log::info('Challenge Score: ' . json_encode($json));
            return \Response::json($json, 200);
        }
        
       
        $resMulti=  \App\Models\MultiPlayerChallengePlayer::select('id','score','won_status')->where('user_id',$param['user_id'])->where('challenge_id',$param['challenge_id'])->first();
        if(is_null($resMulti)){
            $json = \General::error_res('Challenge Not Found');
            return \Response::json($json, 200);
        }

        $res=\General::success_res('Challenge Score');
        $res['data']=$resMulti;
    
        \Log::info('Challenge Score:');
        return \Response::json($res, 200);
    }

}
