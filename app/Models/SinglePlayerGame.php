<?php 

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;
class SinglePlayerGame extends Model {
    protected $fillable = [
       
    ];
    
    protected $table    = 'single_player_game';
    protected $hidden   = [];
    public $timestamps  = true;


    public function user(){
        return $this->hasOne('App\Models\Users','id','player_id');
    }
    
    public function scopeActive($query) {
        return $query->where('status', '=', 1);
    }
    
    public static function add_single_player_game($param='')
    {
        try{

            $myGame = SinglePlayerStatics::getStaticsByUserId($param['user_id']);
            $game=new self;
            $game->bunch_id = $param['bunch_id'];
            $game->player_id=$param['user_id'];
            $game->type=$param['type'];
            $game->match_num=$param['match_num'];
            $game->status=1;  
            $game->current_game_level=  isset($myGame['current_game_level']) ? $myGame['current_game_level'] : 0;
            $game->level_up_down_id=  isset($myGame['level_up_down_id']) ? $myGame['level_up_down_id'] : 0;
            $game->save();
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[1];
            if($errorCode == 1062){
                return \General::error_res('Already Exists');
            }else{
                return \General::error_res('Opps ! Something might wrong.');
            }
        }
        $res= \General::success_res('New  Game Created Successfuly');
        $res['data']['game_id']=$game->id;
        $res['data']['match_num']=$param["match_num"];
        $res['data']['hint_num']=$param["hint_num"];
        $res['data']['level']=$param["level"];
        return $res;
    }
    
    public static function update_single_player_game($param='')
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
                $t_time = $settings['ut']+1;
            }
            
            
            $game=self::active()->where('id',$param['game_id'])->first();
            if(is_null($game)){
                return \General::error_res("Game  Not Found");
            }

            $game->score=$score;
            $game->equation=$equation;
            $game->hint=$hint;
            $game->won_status=$won_status;
            $game->t_time= $t_time;
            $game->time=$time;

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

        $res= \General::success_res('Statics is Successfully  Updated');
        $res['bunch_id'] = $game->bunch_id;
        return $res;
    }
    
    
    public static function skip_single_player_game($param='')
    {
//             dd($param);
        try {
            $game=self::active()->where('id',$param['game_id'])->first();
             if(is_null($game)){

                    return \General::error_res("Game  Not Found");
             }

            $game->skip=1;
            $game->t_time=isset($param['t_time'])?$param['t_time']:'';
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
//            $res['data']['player_challenge_id']=$game->id;
        return $res;
    }
    
    
        
  public static function user_single_player_report($param){
//        dd($param);
        $count=self::where('player_id',$param['user_id'])->orderBy('id','asc');

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
        
        
        $mpReport=self::where('player_id',$param['user_id'])->skip($start)->take($len)->orderBy('id','desc');

        $mpReport = $mpReport->with('user')->get()->toArray();
       
        $res['len']=$len;
        $res['crnt_page']=$crnt_page;
        $res['total_page']=$total_page;
        
        $res['result']=$mpReport;
        $res['flag']=$flag;
//        dd($res);
        return $res;
    }

    public static function filter_single_player_report($param){
//        dd($param);

        $users = self::orderBy('id','desc');
        
//        if(isset($param['name']) && $param['name'] != ''){
//            $users = $users->where(function($q)use($param){
//               $q->where('username','like','%'.$param['name'].'%'); 
//            });
//        }
        
       if(isset($param['user_id']) && $param['user_id'] != ''){
             $users =  $users->where('player_id',$param['user_id']);
        }
        
        if(isset($param['email']) && $param['email'] != ''){
            $users = $users->where('email','like','%'.$param['email'].'%');
        }
        
//        if(isset($param['status']) && $param['status'] != ''){
//            $users = $users->where('status',$param['status']);
//        }
        
        if(isset($param['type']) && $param['type'] != ''){
            $users = $users->where('type',$param['type']);
        }
        

        if(isset($param['won_status']) && $param['won_status']!=''){
          
            $users = $users->where('won_status',$param['won_status']);
        }
     

        
        $count = $users->count();
        
        $len = $param['itemPerPage'];
        $start = ($param['currentPage']-1) * $len;
        
        $users = $users->with('user')->skip($start)->take($len)->get()->toArray();
        $res['data'] = $users;
        $res['total_record'] = $count;
//        dd($users);
        return $res;
    }
}
