<?php 

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;
class MultiPlayerChallengePlayer extends Model {
    protected $fillable = [
       
    ];
    
    protected $table    = 'multi_player_challenge_player';
    protected $hidden   = [];
    public $timestamps  = true;
    


    public function user(){
        return $this->hasOne('App\Models\Users','id','user_id');
    }
    
    public function challenge(){
        return $this->hasOne('App\Models\MultiPlayerChallenge','id','challenge_id');
    }
    public function games(){
        return $this->hasMany('App\Models\MultiPlayerChallengePlayerGame','challenge_player_id','id');
    }

    

    public function scopeActive($query) {
        return $query->where('status', '=', 1);
    }
  
   
        
    public static function add_challenge_player($param='')
    {
        $statics = SinglePlayerStatics::getStaticsByUserId($param['user_id']);
        // dd($param);
         try {
            $game=new self;
            $game->challenge_id=$param['challenge_id'];
            $game->user_id=$param['user_id'];
            $game->current_game_level=  isset($statics['current_game_level']) ? $statics['current_game_level'] : 0;
            $game->status=1;  
            $game->save();
        } catch (\Illuminate\Database\QueryException $e) {
//                       dd($e->getMessage());
//                       \Log::info($e->getMessage());
            $errorCode = $e->errorInfo[1];
                if($errorCode == 1062){

                    return \General::error_res('Already Exists');

                }else{
                    return \General::error_res('Opps ! Something might wrong.');
                }
        }

        $res= \General::success_res('Player Joined  Successfuly');
        $res['data']['challenge_player_id']=$game->id;
        return $res;
    }
    
    public static function update_challenge_player($param='')
    {
            // dd($param);
          try {
                    $game=self::where('status',1)->where('id',$param['player_challenge_id'])->first();
                     if(is_null($game)){
                         
                            return \General::error_res("Game  Not Found");
                     }
                     
                    $game->score=$param['total_score'];
                    $game->status=0;  
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
            $res['data']['player_challenge_id']=$game->id;
            return $res;
    }
    
    public static function get_by_id($id=0){
        $data = self::find($id);
        if($data){
            return $data->toArray();
        }else{
            return [];
        }
    }

    public static function user_multi_player_report($param){
//        dd($param);
        $count=self::where('user_id',$param['user_id'])->orderBy('id','asc');

        $count = $count->count();

        $page=$param['crnt'];
        $len=$param['len'];
        $op=  isset($param['opr'])?$param['opr']:'';
        $total_page=ceil($count/$len);
        $flag=1;
        
        $start=0;
        
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
        
        
        $mpReport=self::where('user_id',$param['user_id'])->skip($start)->take($len)->orderBy('id','desc');

        $mpReport = $mpReport->with('user')->with('challenge')->get()->toArray();
       
        $res['len']=$len;
        $res['crnt_page']=$crnt_page;
        $res['total_page']=$total_page;
        
        $res['result']=$mpReport;
        $res['flag']=$flag;
//        dd($res);
        return $res;
    }
    

     public static function filter_multi_player_report($param){
        
        $users = self::orderBy('id','desc');
        
//        if(isset($param['name']) && $param['name'] != ''){
//            $users = $users->where(function($q)use($param){
//               $q->where('username','like','%'.$param['name'].'%'); 
//            });
//        }
        
        
        if(isset($param['user_id']) && $param['user_id'] != ''){
             $users =  $users->where('user_id',$param['user_id']);
        }
        
        if(isset($param['email']) && $param['email'] != ''){
            $users = $users->where('email','like','%'.$param['email'].'%');
        }
        if(isset($param['status']) && $param['status'] != ''){
            $users = $users->where('won_status',$param['status']);
        }
        
        
        
        $count = $users->count();
        
        $len = $param['itemPerPage'];
        $start = ($param['currentPage']-1) * $len;
        
        $users = $users->with('user')->with('challenge')->skip($start)->take($len)->get()->toArray();
        $res['data'] = $users;
        $res['total_record'] = $count;
        
        return $res;
    }
    public static function updateTemporaryPlayerLevel($challenge_player_id=0){
        $settings = app('settings');
        $tTotalGame=$settings['total_threshold_game'];
        
        $c_player = self::get_by_id($challenge_player_id);
        
        $all_players = self::where('challenge_id',$c_player['challenge_id'])->get();
        
        foreach ($all_players as $player){
            $games = \App\Models\MultiPlayerChallengePlayerGame::where('challenge_player_id',$player->id)->where('level_up_down_id',$player->level_up_down_id)->where('skip',0)->orderBy('id','desc')->take($tTotalGame)->get();
            if($games->count()>=$tTotalGame){
                $tAvg = $games->avg('t_time');
                if($tAvg<$settings['lt'] ){
                    if($player->current_game_level<30){
                       $player->current_game_level=$player->current_game_level+1;
                       $player->level_up_down_id=$player->level_up_down_id+1;
                    }
                }else if($tAvg >$settings['ut']){
                    if($player->current_game_level>1){
                        $player->current_game_level=$player->current_game_level-1;
                        $player->level_up_down_id=$player->level_up_down_id+1;
                    }
                }

                try{
                    $player->save();
                }catch(\Illuminate\Database\QueryException $e){
                    \Log::info($e->getMessage());
                }
            }
        }
    }

}
