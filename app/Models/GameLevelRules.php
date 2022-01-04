<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;

class GameLevelRules extends Model {


    protected $fillable = [
          '','','',
    ];
    
    protected $table = 'game_level_rules';
    
    public function getAvatarAttribute($value) {
        if(isset($value) && $value!='' && file_exists(config('constant.GAME_AVATAR_PATH').'/'.$value)){
           return config('constant.GAME_AVATAR_PATH_LINK').'/'.$value;
        }else{
           return null;
        }
    }

    public function scopeActive($query) {
        return $query->where('status', '=', 1);
    }
    
    public static function getByLevel($level=0){
        $game = self::where('level',$level)->first();
//        dd($game);
        if($game){
            return $game->toArray();
        }else{
            return [];
        }
    }
    public static function getByLevelApi($level=0){
        $game = self::select('level','name','slogan','avatar')->where('level',$level)->first();
//        dd($game);
        if($game){
            return $game->toArray();
        }else{
            return [];
        }
    }
    public static function getGameDataByLevel($level=0){
        $settings=app('settings');
        $data=array();

        $level_obj= self::where('level',$level)->first();
        if($level_obj){
            $data['match_num'] =  mt_rand($level_obj->min_match_num, $level_obj->max_match_num);
            $data['level']     = self::getByLevelApi($level);
            for($i=0;$i<$settings['max_hint_per_game'];$i++){
                $data['hint_num'][$i]= mt_rand(1, $level_obj->hint_range);
            }
        }
        return $data;
    }
    
   
}
