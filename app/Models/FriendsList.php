<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;
use DB;
class FriendsList extends Model {


    protected $fillable = [
          'user_id','email','status',
    ];
    
    protected $table = 'friends_list';



    public function scopeActive($query) {
        return $query->where('status', '=', 1);
    }
    
    public function scopeInactive($query) {
        return $query->where('status', '=', 0);
    }
    
    public function user(){
        return $this->hasOne('App\Models\Users','id','user_id');
    }
    
     public function userMail(){
        return $this->hasOne('App\Models\Users','email','email');
    }
    
    public function userScore(){
        return $this->hasOne('App\Models\MultiPlayerStatics','id','user_id');
    }
    
    
    public static function update_friend_list($param){
        
       \Log::info('update_friends_emails='.json_encode($param['emails']));
        $emails=json_decode($param['emails']);
          
   
        if(!is_array($emails)){
            return   \General::error_res('Emails must in array format');
        }
        
        $userid=$param['user_id'];
        foreach($emails as $em){
             $me_email=Users::where('id',$userid)->first();
             if($em==$me_email->email){
                continue;
             }
             
            try{
                
                $fr = self::where('user_id',$userid)->where("email", $em)->first();
               
                if (is_null($fr)){
                    $fr = new self;
                    $fr->user_id=$userid;
                    $fr->email=$em;
                    
                    $usr=\App\Models\Users::where("email", $em)->first();
                    if(is_null($usr)){
                        $status=0;
                    }else{
                        $status=1;
                    }
                   
                    $fr->status=$status;
                    $fr->save();
                    
                }else{
                    
                    if($fr->status==0){
                        $usr=\App\Models\Users::where("email", $em)->first();
                        if(is_null($usr)){
                            $status=0;
                        }else{
                            $status=1;
                        }
                        
                        $fr->status=$status;
                        $fr->save();
                    }
                }
               
            } catch (\Illuminate\Database\QueryException $e) {
//                        dd($e->getMessage());
                    \Log::info('..........Error in Update friend List..............');
                    \Log::info($e);
                    $errorCode = $e->errorInfo[1];
                    if($errorCode == 1062){
                        return \General::error_res('Already Exists');
                    }else{
                        return \General::error_res('Opps ! Something might wrong.');
                    }
            }
             
        }
        
       return \General::success_res('Friends List Updated'); 
    }
    
    
    
    public static function my_friend_list($param){
               // dd($param);

         if($param['in_system']=='all'){
            $count=self::where('user_id',$param['user_id'])->orderBy('id','asc');    
         }else{
            $count=self::where('status',$param['in_system'])->where('user_id',$param['user_id'])->orderBy('id','asc');
        }
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
                $crnt_page=$page-1;
                $start=($crnt_page-1)*$len;
            }
            else{
                $crnt_page=$page;
                $start=($crnt_page-1)*$len;
            }
        }
        
        if($param['in_system']=='all'){
            $friend_listIn=self::where('user_id',$param['user_id'])->skip($start)->take($len)->orderBy('id','desc');    
        }else{
            $friend_listIn=self::where('status',$param['in_system'])->where('user_id',$param['user_id'])->skip($start)->take($len)->orderBy('id','desc');  
        }
        

        // $friend_listIn = $friend_listIn->with('userMail.userScore')->get()->toArray();

        $friend_listIn = $friend_listIn->select('email','status')->with(['userMail'=>function($query){
            $query->select('id','email','username','avatar','status');   
            },'userMail.userScore'=>function($query){
                $query->select('user_id','total_score');
        }
        ])->get()->toArray();
        if($param['in_system']=='all'){
            $new_frn_list = array();
            $new_frn_list = $friend_listIn;
        }
        else{
            $new_frn_list = array();
            foreach ($friend_listIn as $key => $value) {
             if($value['user_mail']['status']==1){
                array_push($new_frn_list,$value);
             }
        }
        }
        
        $res['len']=$len;
        $res['crnt_page']=$crnt_page;
        $res['total_page']=$total_page;
        $res['result']=$new_frn_list;
        $res['flag']=$flag;

        if(isset($param['robot_req']) && $param['robot_req']==1){
            if($count<$len){
                $rlen=$len-$count;
                $roboUserQuery =  \App\Models\Users::active()->select('id','email','username','robot_status')->
                with(['userMail'=>function($query){
                    $query->select('id','email','username','avatar','status');   
                    },'userMail.userScore'=>function($query){
                        $query->select('user_id','total_score');
                    }
                ])->where('robot_status',1)->where('id','!=',$param['user_id']);
                
                $allUser = $roboUserQuery->take($rlen)->get()->toArray();
                $rCount = count($allUser);
                $allUser=array_merge($res['result'],$allUser);
                $res['result']=$allUser;
                // $res['result']=array();
                // $res['total_page']=  ceil(($count+$roboUserQuery->count())/$len);
                $res['total_page']=  (int) ceil(($count+$rCount)/$len);
                if ($crnt_page > 1) {
                    $res['result']=[];
                }
            }
        }
        
        
      return $res;
        
    }
}
