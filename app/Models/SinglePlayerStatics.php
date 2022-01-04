<?php 

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;
class SinglePlayerStatics extends Model {
    protected $fillable = [
       
    ];
    
    protected $table    = 'single_player_statics';
    protected $hidden   = [];
    public $timestamps  = true;
    


    public function user(){
        return $this->hasOne('App\Models\Users','id','user_id');
    }
    
    public function scopeActive($query) {
        return $query->where('status', '=', 1);
    }
  
   
        
    public static function add_single_player_statics($param='')
    {
            // dd($param);
             try {
                    $game=new self;
                    $game->user_id=$param['user_id'];
                    $game->current_game_level=$param['current_game_level'];
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
            return $res;
    }
    
    public static function update_single_player_statics($param='')
    {
      
             try {
                    $game=self::where('id',$param['game_id'])->first();
                     if(is_null($game)){
                         
                            return \General::error_res("Game  Not Found");
                     }

                    $game->current_game_level=$param['score'];
                    $game->save();
                    
            } catch (\Illuminate\Database\QueryException $e) {
//                   dd($e->getMessage());
                     return \General::error_res('Opps ! Something might wrong.');
                  
            }

            $res= \General::success_res('Statics is Successfully  Updated');
            return $res;
    }
    
    
    public static function skip_single_player_game($param='')
    {

             try {
                    $game=self::where('id',$param['game_id'])->first();
                     if(is_null($game)){
         
                            return \General::error_res("Game  Not Found");
                     }

                    $game->skip=1;  
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
            return $res;
    }
    
    public static function getStaticsByUserId($user_id=0){
        $data = self::where('user_id',$user_id)->first();
        if($data){
            return $data->toArray();
        }else{
            return [];
        }
    }

    public static function filter_single_player_statics($param){
        
       $report = self::orderBy('id','desc');
        
//       if(isset($param['name']) && $param['name'] != ''){
//           $report =$report->where(function($q)use($param){
//               $q->where('username','like','%'.$param['name'].'%'); 
//            });
//        }
       
        if(isset($param['user_id']) && $param['user_id'] != ''){
             $report =  $report->where('user_id',$param['user_id']);
        }
        
        
        if(isset($param['start_date']) && $param['start_date'] != '' && isset($param['end_date']) && $param['end_date'] != ''){
            $report = $report->whereBetween('updated_at', array($param['start_date'], $param['end_date']));
        }
        
        $count =$report->count();
        
        $len = $param['itemPerPage'];
        $start = ($param['currentPage']-1) * $len;
        
       $report =$report->with('user')->skip($start)->take($len)->get()->toArray();
        $res['data'] =$report;
        $res['total_record'] = $count;
        
        return $res;
    }
}
