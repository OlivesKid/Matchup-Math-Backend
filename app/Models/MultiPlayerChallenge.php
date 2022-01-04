<?php 

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;
class MultiPlayerChallenge extends Model {
    protected $fillable = [
        'created_user_id','total_participant','status'
    ];
    
    protected $table    = 'multi_player_challenge';
    protected $hidden   = [];
    public $timestamps  = true;
    

    public function user(){
        return $this->hasOne('App\Models\Users','id','challenger_user_id');
    }
    
    public function wonuser(){
        return $this->hasOne('App\Models\Users','id','won_user_id');
    }
    
    public function scopeActive($query) {
        return $query->where('status', '=', 1);
    }
  

    public static function add_challenge($param='')
    {
        // dd($param);
            $settings=app('settings');
            if($param['type']==1){
                 $date = new \DateTime($settings['multi_player_game_timed_expire']);
            }elseif($param['type']==0){
                $date = new \DateTime($settings['multi_player_game_classic_expire']);
            }

          $endTime=$date->format('Y-m-d H:i:s');
//              dd($endTime);
         try {

                $game=new self;
                $game->challenger_user_id=$param['user_id'];
                $game->end_time=$endTime;
                $game->type=$param['type'];
                $game->status=1;  
                $game->save();


        } catch (\Illuminate\Database\QueryException $e) {

            $errorCode = $e->errorInfo[1];
                if($errorCode == 1062){
                    return \General::error_res('Already Exists');
                }else{
                    return \General::error_res('Opps ! Something might wrong.');
                }
        }

        $res= \General::success_res('Success');
        $res['data']=$game->id;
        return $res;
    }

    public static function update_challenge($param='')
    {
        // dd($param);
         $date = new \DateTime('+1 day');
         $settings=app('settings');
         $latestDrawTime=$date->format('Y-m-d');
         
        try {
                $draw=self::where('id',$param['id'])->first();
                $draw->status=1; 
               
                $draw->save();
        } catch (\Illuminate\Database\QueryException $e) {
                   
            $errorCode = $e->errorInfo[1];
                if($errorCode == 1062){
                    return \General::error_res('Already Exists');
                }else{
                    return \General::error_res('Opps ! Something might wrong.');
                }
        }

        $res= \General::success_res('Success');
        $res['data']=$draw->id;
        return $res;
    }

    
    
    public static function update_won_challenge($param='')
    {
        try {
            $draw=self::where('id',$param['id'])->first();
            $draw->won_user_id=$param['won_id']; 
            $draw->status=0; 
           
            $draw->save();
        } catch (\Illuminate\Database\QueryException $e) {
                   
            $errorCode = $e->errorInfo[1];
            if($errorCode == 1062){
                return \General::error_res('Already Exists');
            }else{
                return \General::error_res('Opps ! Something might wrong.');
            }
        }

        $res= \General::success_res('Success');
        $res['data']=$draw->id;
        return $res;
    }
    
    
    

    public static function filter_game_report($param){
       
        $report = self::orderBy('id','desc');
        if(isset($param['user_id']) && $param['user_id'] != ''){
            $report = $report->where('user_id',$param['user_id']);
        }

        if(isset($param['game_id']) && $param['game_id'] != ''){
            $report = $report->where('id',$param['game_id']);
        }
        
        if(isset($param['type']) && $param['type'] != ''){
            $report = $report->where('type',$param['type']);
        }


        if(isset($param['start_date']) && $param['start_date'] != '' && isset($param['end_date']) && $param['end_date'] != ''){
            $report = $report->whereBetween('created_at', array($param['start_date'], $param['end_date']));
        }
        
        $count = $report->count();
        
        $len = $param['itemPerPage'];
        $start = ($param['currentPage']-1) * $len;
        
        $report = $report->with('user')->skip($start)->take($len)->get()->toArray();
        $res['data'] = $report;
        $res['total_record'] = $count;
        
        return $res;
    }
    
      public static function filter_challenege_report($param){
        
        $users = self::orderBy('id','desc');
        
//        if(isset($param['name']) && $param['name'] != ''){
//            $users = $users->where(function($q)use($param){
//               $q->where('username','like','%'.$param['name'].'%'); 
//            });
//        }
        
        
        if(isset($param['user_id']) && $param['user_id'] != ''){
             $users =  $users->where('challenger_user_id',$param['user_id']);
        }
        
        if(isset($param['type']) && $param['type'] != ''){
            $users = $users->where('type',$param['type']);
        }
        
        if(isset($param['status']) && $param['status'] != ''){
            $users = $users->where('won_status',$param['status']);
        }
        
        if(isset($param['start_date']) && $param['start_date'] != '' && isset($param['end_date']) && $param['end_date'] != ''){
            $users = $users->whereBetween('created_at', array($param['start_date'], $param['end_date']));
        }
        
        
        $count = $users->count();
        
        $len = $param['itemPerPage'];
        $start = ($param['currentPage']-1) * $len;
        
        $users = $users->with('user')->with('wonuser')->skip($start)->take($len)->get()->toArray();
        $res['data'] = $users;
        $res['total_record'] = $count;
        
        return $res;
    }
    
    
}
