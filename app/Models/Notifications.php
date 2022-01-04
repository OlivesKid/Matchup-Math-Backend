<?php 

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;
class Notifications extends Model {
    protected $fillable = [
       
    ];
    
    protected $table    = 'notification';
    protected $hidden   = [];
    public $timestamps  = true;
    


    public function user(){
        return $this->hasOne('App\Models\Users','id','user_id');
    }
    
    public function resp_user(){
        return $this->hasOne('App\Models\Users','id','responder_user_id');
    }
    
    public function challenge(){
        return $this->hasOne('App\Models\MultiPlayerChallenge','id','challenge_id');
    }
    
    public function scopeActive($query) {
        return $query->where('status', '=', 1);
    }
  
   
        
    public static function add_notification($param='')
    {
            // dd($param);
             try {
                    $notify=new self;
                    $notify->user_id=$param['user_id'];
                    $notify->notification_msg=$param['notify_msg'];
                    
                    if(isset($param['type'])){
                        $notify->type=$param['type'];
                    }
                    
                    if(isset($param['challenge_id'])){
                        $notify->challenge_id=$param['challenge_id'];
                    }
                    
                    if(isset($param['responder_user_id'])){
                        
                        $notify->responder_user_id=$param['responder_user_id'];
                    }
                    
                    $notify->save();
                    
            } catch (\Illuminate\Database\QueryException $e) {
//                  dd($e->getMessage());
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
    
    public static function update_notification($param='')
    {
      
             try {
                    $notify=self::where('id',$param['notification_id'])->first();
                     if(is_null($notify)){
                         
                            return \General::error_res("Notification Id  Not Found");
                     }

                    $notify->status=$param['status'];  
                    $notify->save();
                    
            } catch (\Illuminate\Database\QueryException $e) {
//                       dd($e->getMessage());
                     $errorCode = $e->errorInfo[1];
                    if($errorCode == 1062){
                        
                        return \General::error_res('Already Exists');
                        
                    }else{
                        return \General::error_res('Opps ! Something might wrong.');
                    }
            }

            $res= \General::success_res('Notification is Successfully  Updated');
            return $res;
    }
    
    public static function delete_notification($param='')
    {
      
             try {
                    $notify=self::where('id',$param['notification_id'])->first();
                     if(is_null($notify)){
                            return \General::error_res("Notification Id  Not Found");
                     }
                    $notify->delete();
                    
            } catch (\Illuminate\Database\QueryException $e) {
//                       dd($e->getMessage());
                    $errorCode = $e->errorInfo[1];
                    if($errorCode == 1062){
                        return \General::error_res('Already Exists');
                    }else{
                        return \General::error_res('Opps ! Something might wrong.');
                    }
            }

            $res= \General::success_res('Notification is Successfully  Deleted');
            return $res;
    }
    
    
    public static function get_notification_report($param){
        \Log::info('user id: '.$param['user_id']);
        $count  = self::where('user_id',$param['user_id'])->orderBy('id','desc');
        // dd($param['user_id']);
        $count  = $count->count();
        
        $page   = $param['crnt'];
        $len    = $param['len'];
        $op     = isset($param['opr'])?$param['opr']:'';
        $total_page = ceil($count/$len);
        $flag   = 1;
        
        $start  = 0;
        
        if($op != ''){
            if($op == 'first'){
                $crnt_page = 1;
                $start = ( $crnt_page - 1 ) * $len;
            }
            
            elseif($op == 'prev'){
                $crnt_page = $page - 1;
                if($crnt_page <= 0){
                    $crnt_page = 1;
                }
                $start = ($crnt_page - 1) * $len;
            }

            elseif($op == 'next'){
                $crnt_page = $page + 1;
                if($crnt_page >= $total_page){
                    // $crnt_page = $total_page;
                }
                $start = ($crnt_page - 1) * $len;
            }

            else{
                $crnt_page = $total_page;
                $start  = ($crnt_page - 1) * $len;
            }
        }

        else{
            if($page > $total_page){
//                $flag=0;
                $crnt_page = $page - 1;
                $start = ($crnt_page - 1) * $len;
            }
            else{
                $crnt_page = $page;
                $start = ($crnt_page - 1) * $len;
            }
        }
        
        
        $notifyReport = self::select('id','type','status','challenge_id','notification_msg','responder_user_id','created_at')->where('user_id',$param['user_id'])->skip($start)->take($len)
            ->with(['challenge'=>function($query){
                    $query->select('id','type');
                        }])->with(['resp_user'=>function($query){
                            $query->select('id','email','avatar');
                        }])->orderBy('id','desc');
      

        $notifyReport = $notifyReport->get()->toArray();

        $totalUnRead=self::active()->where('user_id',$param['user_id'])->orderBy('id','desc')->count();
        
        foreach($notifyReport as $vr)
        {
            $notifyu=self::where('id',$vr['id'])->first();
            $notifyu->status=0;
            $notifyu->save();
        }
        
       
        
        $res['len']         = $len;
        $res['crnt_page']   = $crnt_page;
        $res['total_page']  = $total_page;
        $res['total_unread_notification']  = $totalUnRead;
        
        $res['result']  = $notifyReport;
        $res['flag']    = $flag;
        return $res;
    }

    
    
    
    public static function filter_notification_report($param){
        
       $report = self::orderBy('id','desc');
        

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
