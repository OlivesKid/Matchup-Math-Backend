<?php 

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;
class MultiPlayerChallengePlayerGame extends Model {
    protected $fillable = [
       
    ];
    
    protected $table    = 'multi_player_challenge_player_game';
    protected $hidden   = [];
    public $timestamps  = true;
    


    public function user(){
        return $this->hasOne('App\Models\Users','id','user_id');
    }
    
    public function scopeActive($query) {
        return $query->where('status', '=', 1);
    }
  
   
        
    public static function add_challenge_player_game($param='')
    {
            // dd($param);
        try {
            $player=MultiPlayerChallengePlayer::where('id',$param['challenge_player_id'])->first()->toArray();

            $game=new self;
            $game->challenge_player_id=$param['challenge_player_id'];
            $game->match_num=$param['match_num'];
            // if(isset($param['level']['level'])){
            //     $game->current_game_level=$param['level']['level'];    
            // }
            $game->current_game_level=$param['level']['level'];    
            $game->level_up_down_id=$player['level_up_down_id'];
//                    $game->status=1;  
            $game->save();
       } catch (\Illuminate\Database\QueryException $e) {
//            dd($e->getMessage());
            $errorCode = $e->errorInfo[1];
            if($errorCode == 1062){
                return \General::error_res('Already Exists');
            }else{
                return \General::error_res('Opps ! Something might wrong.');
            }
       }

        $res= \General::success_res('New Challenge Game Created Successfuly');
        $res['data']['challege_game_id']=$game->id;
        $res['data']['match_num']=$param['match_num'];
        $res['data']['hint_num']=$param['hint_num'];
        $res['data']['level']=$param['level'];
        $res['data']['total_played']=$param['total_played'];
       return $res;
    }
    
    public static function update_challenge_player_game($param='')
    {
        try {
            $settings= app('settings');
        
            $score = isset($param['score']) ? $param['score'] : 0;
            $equation = isset($param['equation']) ? $param['equation'] : "";
            $hint = isset($param['hint']) ? $param['hint'] : 0;
            $won_status = isset($param['won_status']) ? $param['won_status'] : 0;
            $time = isset($param['time_check']) ? $param['time'] : 0;
            $t_time = isset($param['t_time']) ? $param['t_time'] : 0;
            if($won_status==0 && $t_time<$settings['lt']){
                $t_time = $settings['lt'];
            }
            
            $game=self::where('id',$param['challenge_game_id'])->first();
            if(is_null($game)){
                return \General::error_res("Game  Not Found");
            }

            $game->score=$score;
            $game->equation=$equation;
            $game->hint=$hint;
            $game->won_status=$won_status;
            $game->t_time=$t_time;
            $game->time=$time;
//                    $game->status=0;  
            $game->save();

        } catch (\Illuminate\Database\QueryException $e) {
//            dd($e->getMessage());
            $errorCode = $e->errorInfo[1];
            if($errorCode == 1062){
                return \General::error_res('Already Exists');
            }else{
                return \General::error_res('Opps ! Something might wrong.');
            }
        }

        $res= \General::success_res('Success');
        $res['data']['player_game_id']=$game;
        return $res;
    }
    
    
    public static function skip_challenge_player_game($param='')
    {
//             dd($param);
             try {
                    $game=self::where('id',$param['challenge_game_id'])->first();
                     if(is_null($game)){
         
                            return \General::error_res("Game  Not Found");
                     }

                    $game->skip=1;
                    $game->t_time=isset($param['t_time'])?$param['t_time']:'';
                    $game->save();
                    
            } catch (\Illuminate\Database\QueryException $e) {
//                       dd($e->getMessage());
                $errorCode = $e->errorInfo[1];
                    if($errorCode == 1062){
                        
                        return \General::error_res('Already Exists');
                        
                    }else{
                        return \General::error_res('Opps ! Something might wrong.');
                    }
            }

            $res= \General::success_res('Success');
            $res['data']['player_game_id']=$game->id;
            return $res;
    }
}
