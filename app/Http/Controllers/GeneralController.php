<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Lib\coin\jsonRPCClient;
use Symfony\Component\HttpFoundation\Request;
use DB;
use FormulaParser\FormulaParser;


class GeneralController extends Controller {

    function index(){
        
    }
    public function declareChallengeResult(){
        
        $settings=app('settings');
        $curTime=date('H:i:s');
        $curDate=date('Y-m-d');
        $curDateTime=date('Y-m-d H:i');
   
        
        $resCng=  \App\Models\MultiPlayerChallenge::active()->where('end_time','<=',$curDateTime)->get()->toArray();
        
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
                   
                 $mpUser=  \App\Models\MultiPlayerStatics::where('user_id',$ap['user_id'])->first();
                 
                  if(!is_null($mpUser)){
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
                  if(!is_null($mpUser)){
                    $mParam['user_id']=$mpUser->user_id;
                    $mParam['played']=$mpUser->played+1;
                    $mParam['won']=$mpUser->won+1;
                    $mParam['tie']=$mpUser->tie+0;
                    $mParam['lost']=$mpUser->lost+0;
                    $mParam['score']=$mpUser->total_score+$allPlayer[0]['score'];
                   
                    $resMS=\App\Models\MultiPlayerStatics::update_multi_player_statics($mParam);
                    $wonid=$mpUser->user_id;

                    $user_list=\App\Models\Users::select('id','device_type','device_token')->active()->where('id',$mpUser->user_id)->get()->toArray();
                  }
                   

                    $resMP=\App\Models\MultiPlayerChallengePlayer::where('id',$allPlayer[0]['id'])->update(['won_status'=>1]);
                    
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
                    if(!is_null($mpUser)){
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
    
    public function acceptChallenge($chid='',$email=''){
        $uid=\App\Models\Users::where('email',$email)->first();
        if(is_null($uid)){
             $json= \General::error_res('User Not Found');
             return \Response::json($json, 200);
        }
        
        $cid=  \App\Models\MultiPlayerChallenge::where('id',$chid)->first();
        if(is_null($cid)){
            
             $json= \General::error_res('Challenge Not Found');
             return \Response::json($json, 200);
        }
        
        $param['user_id'] = $uid->id;
        $param['challenge_id'] = $chid;
        
        \Log::info('Robot Join as Friend : ' . json_encode(\Input::all()));
        
        $res=\App\Models\MultiPlayerChallengePlayer::add_challenge_player($param);

        \Log::info('Join Friend Response :');
        return $res;

    }
    
    // public static  function randomMatchNumber(){
    //      $settings=app('settings');
    //      $mrange=  explode('-', $settings['match_num_range']);
    //      return mt_rand($mrange[0],$mrange[1]);
    // }
    public static function randomMatchNumber($type, $challnger_id = null) {
        $settings = app('settings');
        $user = app("logged_in_user");
        if ($type == "multi") {
            $tplayer = \App\Models\MultiPlayerChallengePlayer::where('id', $challnger_id)->first();
            $levelID = \App\Models\MultiPlayerChallengePlayer::where('challenge_id', $tplayer->challenge_id)->max('current_game_level');
        } elseif ($type == "single") {
            $levelID = \App\Models\SinglePlayerStatics::where('user_id', $user['id'])->value('current_game_level');
        }
        $dataLevel = \App\Models\GameLevelRules::getGameDataByLevel($levelID);
        \Log::info("Level Object :" . json_encode($dataLevel));
        return $dataLevel;
    }

    
    public function robotChallengePlayerGame(){

        $settings= app('settings');
        
        //$robo_mail=explode(',',$settings['robot_email']);
        //$uid=\App\Models\Users::whereIn('email',$robo_mail)->get()->toArray();
        
        $uid=\App\Models\Users::active()->where('robot_status',1)->get()->toArray();
        if(count($uid)<1){
             $json= \General::error_res('User Not Found');
             return \Response::json($json, 200);
        }
        
//          dd($uid);
        $uids=\General::get_field_array($uid,'id');

        $robotPlayers=\App\Models\MultiPlayerChallengePlayer::active()->whereIn('user_id',$uids)->get()->toArray();
        // dd($robotPlayers);
        if(count($robotPlayers)<1){
            return \General::error_res('No Robot Game Found');
        }
        
        foreach($robotPlayers as $rp){

            for($i=0;$i<$settings['total_game_per_challenge'];$i++){
                
//                    $param['user_id'] = $rp['id'];
//                    $param['challenge_player_id'] = $user['id'];

                    \Log::info('Robot Player Game : '.json_encode(\Input::all()));

                    $tplayed=\App\Models\MultiPlayerChallengePlayer::active()->where('id',$rp['id'])->first();
                    // dd($tplayed,$settings['total_game_per_challenge'],$settings['hint_range']);
                    if($tplayed->game_played==$settings['total_game_per_challenge']){
                           continue;
//                         $json = \General::error_res('You Had Played Your all Games,wait for result');
//                         return \Response::json($json, 200);
                    }

                    $hrange= explode('-', $settings['hint_range']);
                    $hintRand= mt_rand($hrange[0],$hrange[1]);
                    
                    $winRand=  mt_rand(0, 1);
                    $eq='';
                    if($winRand==0){
                            $eq='10+9-8/2';
                            $matchNum='55';
                            $score=0;
                            $wonStatus=0;
                    }else{
                        
                            $weq=mt_rand(1,4);
                            
                            switch($weq) {

                                case 1:$eq=$hintRand.'+'.$hintRand.'-'.$hintRand.'/'.$hintRand.'*'.$hintRand;
                                            break;
                                case 2:$eq=$hintRand.'-'.$hintRand.'+'.$hintRand.'/'.$hintRand.'*'.$hintRand;
                                            break;
                                case 3:$eq=$hintRand.'*'.$hintRand.'-'.$hintRand.'/'.$hintRand.'+'.$hintRand;
                                            break;
                                case 4:$eq=$hintRand.'+'.$hintRand.'/'.$hintRand.'-'.$hintRand.'*'.$hintRand;
                                            break;
                                default :
                                                $mrange=  explode('-', $settings['match_num_range']);
                                                $eq=$mrange[0].'+'.$mrange[0];
                            }
                            
                             try {
                                    $parser = new FormulaParser($eq,1);
                                    $result = $parser->getResult();
                                    $equResult=(int)$result[1];
                                    $matchNum=abs($equResult);
                            } catch (\Exception $e) {
                                    $res = \General::error_res($e->getMessage());
                                    return \Response::json($res, 200);
                            }
                            $score=100;
                            $wonStatus=1;
                    }
                  
//                  $rnum =  self::randomMatchNumber();
                    // dd($rp);
                    $resRange = self::randomMatchNumber('multi', $rp['id']);
                    // dd($resRange);
                    if (!empty($resRange)) {
                        $game_data = [
                            "challenge_player_id" => $rp['id'],
                            "match_num" => $resRange['match_num'],
                            "hint_num" => $resRange['hint_num'],
                            "level" => $resRange['level'],
                            "total_played" => $tplayed->game_played,
                        ];
                        $res = \App\Models\MultiPlayerChallengePlayerGame::add_challenge_player_game($game_data);
                        // dd($res);
                    }
                    // $param['match_num']=$matchNum;
                    // $param['challenge_player_id']=$rp['id'];
                    // $param['level']['level'] = $rp['current_game_level'];
                    // // $param['hint_num'] = 0;  
                    // $res=\App\Models\MultiPlayerChallengePlayerGame::add_challenge_player_game($param);

                    $param['challenge_game_id']=$res['data']['challege_game_id'];
                    $param['equation']= $eq;
                    $param['hint']='2';
                    $param['score']=$score;
                    $param['won_status']=$wonStatus;
                    
                    $res=\App\Models\MultiPlayerChallengePlayerGame::update_challenge_player_game($param);
                    
            }

            \Log::info('End Robot  Player Game :');
            $res=\General::success_res('All robot played');
            return \Response::json($res, 200);
        }
        
    }

    public function Notification(){

        $NotificationThreeDay = ['Keeping mentally active can help maintain cognitive function. So can sleep. Play MatchUp daily and dream about playing when sleeping.',
            'Math trains the brain and builds the neural pathways that make the brain stronger for all other things. MatchUp Math, we are a mental gym for your brain.',
            'We don’t know if your friends can beat you in game of MatchUp Math but we suggest you challenge them to find out. Play a game of MatchUp today!',
            'Advanced level math adds 11% to earnings, study finds. No other A-level subject attracted such a wage premium. MatchUp Math; we’d like to help you earn more!',
            'Did you know Mount Everest weighs an estimated 357 trillion pounds? Math helps discover amazing things. Play MatchUp and discover your brilliance.',
            'Math is one of the true breakout skill areas that can lead to infinite success in a variety of areas. MatchUp Math, a fun way to develop your math skills.'
            ];

            $weeklyNotification = ['Are you having fun playing MatchUp Math? Please share your enjoyment with friends on your social media pages.'];

            $notification_title = 'MATcHup Math';
            $date = \Carbon\Carbon::now();
            $day = $date->format('d');
            $weekday = $date->dayOfWeek; 

            $user_list=\App\Models\Users::select('id','device_type','device_token')->where('device_type',1)->orWhere('device_type',2)->get()->toArray();
            //monthly notification
            if($day == '1'){
                foreach ($user_list as $send_user){
                  
                  $MonthlyNotification = ['Hello '.$send_user["username"].' , you haven’t played MatchUp in a while. We’ve missed you. Would you like to play now?'];
                  $meta = array($NotificationThreeDay);
                  $device_tokens = array($send_user['device_token']);
                  $msg = $MonthlyNotification;
                  $title = array($notification_title);
                  $badge = array(1);
                  $screen = array();
                  $meta = array(5);
                  $img = array();
                  $res = \App\Lib\FCM::send_notification_with_data($device_tokens,$msg,$send_user['device_type'],$title);
                  // if($send_user['device_type']==config('constant.ANDROID_APP_DEVICE')){
                  //   $res = \App\Lib\Push::android_push($device_tokens,$msg,$title,$badge,$screen,$meta,[],$img);
                  // }    
                  // elseif($send_user['device_type']==config('constant.IPHONE_APP_DEVICE')){
                  //   $res = \App\Lib\Push::iphone_push($device_tokens,$msg,$title,$badge,$screen,$meta,[],$img);
                  // }
                }
            }
            //weekly Notification
            elseif ($weekday == '1') {
              
                foreach ($user_list as $send_user){
                  
                  $meta = array();
                  $device_tokens = array($send_user['device_token']);
                  $msg = $weeklyNotification;
                  $title = array($notification_title);
                  $badge = array(1);
                  $screen = array();
                  $meta = array(5);
                  $img = array();
                  $res = \App\Lib\FCM::send_notification_with_data($device_tokens,$msg,$send_user['device_type'],$title);
                  // if($send_user['device_type']==config('constant.ANDROID_APP_DEVICE')){
                  //   $res = \App\Lib\Push::android_push($device_tokens,$msg,$title,$badge,$screen,$meta,[],$img);
                  // }    
                  // elseif($send_user['device_type']==config('constant.IPHONE_APP_DEVICE')){
                  //   $res = \App\Lib\Push::iphone_push($device_tokens,$msg,$title,$badge,$screen,$meta,[],$img);
                  // }
                }
            }
            //Random notification after every 3 day
            elseif ($day%3 == 0) {
                $key = array_rand($NotificationThreeDay);

                foreach ($user_list as $send_user){
                  
                  $meta = array();
                  $device_tokens = array($send_user['device_token']);
                  $msg = array($NotificationThreeDay[$key]);
                  $title = array($notification_title);
                  $badge = array(1);
                  $screen = array();
                  $meta = array(5);
                  $img = array();
                  $res = \App\Lib\FCM::send_notification_with_data($device_tokens,$msg,$send_user['device_type'],$title);
                  // if($send_user['device_type']==config('constant.ANDROID_APP_DEVICE')){
                  //   $res = \App\Lib\Push::android_push($device_tokens,$msg,$title,$badge,$screen,$meta,[],$img);
                  // }    
                  // elseif($send_user['device_type']==config('constant.IPHONE_APP_DEVICE')){
                  //   $res = \App\Lib\Push::iphone_push($device_tokens,$msg,$title,$badge,$screen,$meta,[],$img);
                  // }
                }
            }
        // $Notification = json_encode($Notification);
      }
    
}