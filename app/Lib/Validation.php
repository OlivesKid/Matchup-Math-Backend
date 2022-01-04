<?php

namespace App\lib;

class Validation {

    private static $rules = array(
        
        "user" => [
           
           
            "fblogin" => [
                'email' => 'required',
            ],
            "APIlogin" => [
                'user_email'    => 'required|min:2|max:50',
                'user_pass' => 'required|min:1|max:25',
                'type' => 'required|in:normal,facebook,google',
                'device_token'  => 'required',
            ],
        
            "signup" => [
                'name'      => 'required|min:2|max:50',
                'email'     => 'required|email|unique:users,email|min:2|max:50',
                // 'mobile'    => 'required|numeric|unique:users,mobileno|digits_between:10,10',
                'password'  => 'required|min:6|max:25',
                'con_password' => 'required|min:1|max:25',
                'device_token' => 'required',
            ],
       
            "update" => [
//                'user_id' => 'required',
                'user_name'     => 'required',
                'user_email'    => 'required|email',
                'user_mobileno' => 'required|min:10|max:25',
            ],
             "update_profile"  => [
                'username'     => 'sometimes|required|min:2',
                'dob'          => 'sometimes|required|digits:4|',
                'avatar'       => 'sometimes|required',
            ],
             "update_email" => [
                'email'     => 'required|email',
                'password'      => 'required',
            ],
            "change_password" => [
                'old_password'      => 'required|min:2|max:25',
                'new_password'      => 'required|min:2|max:25',
                'confirm_password'  => 'required|min:2|max:25',
            ],
            "forget_pass" => [
                'email' => 'required|email',
            ],
            "reset_pass" => [
                'new_pass'  => 'required|min:6',
                'cnew_pass' => 'required',
            ],
            
            "invitation" => [
                'emails'  => 'required',
                'msg'  => 'required',
            ],
            "play_invitation" => [
                'emails'  => 'required',
                'challenge_id'=>'required|numeric',
                'msg'=>'required'
            ],
            "update_notification" => [
                'notification_id'  => 'required',
                'status'=>'required|numeric',
                
            ],
            "delete_notification" => [
                'notification_id'  => 'required',
            ],
            "clear_notification" => [
                'notification_ids'  => 'required',
            ],
        ],
        "friends" => [
            "emails_list" => [
                'emails' => 'required',
                
            ],
        ],
         "pagination" => [
            "api_pagination" => [
                'crnt' => 'required|numeric',
                'len' => 'required|numeric',
                'opr' => 'required',
                
            ],
        ],
         "game" => [
            "gameid" => [
                'challenge_id' => 'required',
            ],
            "challenge_info"=>[
                'challenge_id' => 'required',
            ],
             
            "add_game" => [
                'type' => 'required',
            ],
            "update_score" => [
                
                'challenge_player_id' => 'required',
                'total_score' => 'required',
            ],
            "add_challenge_game" => [
                
                'challenge_player_id' => 'required',
                
            ],
            "update_challenge_game" => [
                
                'challenge_game_id' => 'required',
                'equation' => 'required',
                'hint' => 'required|numeric',
                't_time' => 'required|numeric',
                
            ],
            "skip_challenge_game" => [
                'challenge_game_id' => 'required',
                'challenge_player_id' => 'required',
            ],
            "add_single_game" => [
                'type' => 'required',
            ],
             
            "update_single_game" => [
                'game_id' => 'required',
                'equation' => 'required',
                'hint'=>'required|numeric',
                't_time' => 'required|numeric',
            ],
            "add_hint" => [
                'challenge_game_id' => 'sometimes|required',
                'game_id' => 'sometimes|required',
            ],
            "challenge_score" =>[
                'challenge_id' =>'required',
            ],
             
        ],
        
        "admin" => [
            "login" => [
                'uname' => 'required|min:2|max:50',
                'password' => 'required|min:2|max:25',
           
            ],
            "user_id" => [
                'delete_id' => 'required|numeric|exists:users,id',
            ],
        ]
    );

    public static function get_rules($type, $rules_name) {
        if (isset(self::$rules[$type][$rules_name]))
            return self::$rules[$type][$rules_name];
        return array();
    }

}
