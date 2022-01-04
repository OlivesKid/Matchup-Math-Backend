<?php 

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;
class SinglePlayerTimeMasterBunch extends Model {
    protected $fillable = [
       
    ];
    
    protected $table    = 'single_player_time_master_bunch';
    protected $hidden   = [];
    public $timestamps  = true;

    public function user(){
        return $this->hasOne('App\Models\Users','id','user_id');
    }
    public function scopeActive($query) {
        return $query->where('status', '=', 1);
    }
        
    public static function add_game_record($param='')
    {
        try{
            $settings = app('settings');

            $game = new self;
            $game->user_id = $param['user_id'];
            $game->time_start = date('Y-m-d H:i:s');
            $game->time_limit = $settings['time_master_single_player_time'];
            $game->status = 1;
            $game->save();

        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[1];
            if($errorCode == 1062){
                return \General::error_res('Already Exists');
            }else{
                return \General::error_res('Opps ! Something might wrong.');
            }
        }
        $res = \General::success_res('New Single Player Time master Game Created Successfuly');
        $res['data']['bunch_id']=$game->id;
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
        return $res;
    }
}
