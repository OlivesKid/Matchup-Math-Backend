@extends('layouts.admin')
@section('content')
<?php
$user = $body['user'];
?>
    <script>
        var url = '{{URL::to("admin/user-detail")}}';
        var user_id = '{{$user["id"]}}';
        var pCrnt;
        window.onload=function(){
            $("#<?php echo $body['id']?>").addClass("active");
//            var crnt = $('#crnt').val();
//            var len = $('#len').val();
//            var type = $('#type').html();
//            $('#cntrlbtn').css('display','none');
            
//            filterData(url);

            var crnt = $('#crnt').val();
            
            var len = $('#len').val();
            var type = $('#type').html();
            $('#cntrlbtn').css('display','none');
            var extra = {user_id:user_id};
            getData('admin/user-multi-player-report-filter',crnt,len,'','','',extra);
            
            div_id = 'btable';
            getData('admin/user-single-player-report-filter',crnt,len,'','','',extra);

//            div_id ='';
        };
        $(document).ready(function(){
            $('#user_status').change(function(){
                var status = $(this).val();
                var data = {user_id:user_id,status:status};
                var url = '{{URL::to("admin/change-user-status")}}';
                postAjax(url,data,function(res){
                    Toast(res.msg);
                });
            });
            $('#robot_switch').change(function(){
                var checked = $(this).prop('checked');
                if(checked){
                  var status = 1;
                }else{
                  var status = 0;
                }
                var data = {user_id:user_id,status:status};
                var url = '{{URL::to("admin/change-user-robot-status")}}';
                postAjax(url,data,function(res){
                    Toast(res.msg);
                });
            });
            $('#edit_user_btn').click(function(){
                $('#edit_user_div').css({display:'block'});
                $('#mp_report_div').css({display:'none'});
                $('#sp_report_div').css({display:'none'});
              
            });
            
            $('#mp_report').click(function(){
                $('#edit_user_div').css({display:'none'});
                $('#mp_report_div').css({display:'block'});
                $('#sp_report_div').css({display:'none'});
           
            });
            $('#sp_report').click(function(){
                $('#edit_user_div').css({display:'none'});
                $('#mp_report_div').css({display:'none'});
                $('#sp_report_div').css({display:'block'});
              
            });
           
            
            $('#edit_user').click(function(){
                var user_name = $('#user_name').val();
                var user_email = $('#user_email').val();
                
                if(user_name == ''){
                    Toast('Please enter user name.');
                    return false;
                }
                if(user_email == ''){
                    Toast('Please enter user email');
                    return false;
                }
                
                $('#updateInfo').ajaxForm(function(res){
                    Toast(res.msg);
                    if(res.flag == 1){
                        location.reload();
                    }
                }).submit();
            });
            
            
            $(".crnt").focus(function(){
                pCrnt=$(this).val();
            });
            $(".crnt").focusout(function(){
                var tmpCrnt=$(this).val();
                var isnum = /^\d+$/.test(tmpCrnt);
                var typ = $(this).data('type');
                var clas = '';
                var url ='';
                var id ='';
                if(typ  == 'mp'){
                    url = 'admin/user-multi-player-report-filter';
                    clas = 'coins';
                    id = 'total';
                }else if(typ == 'sp'){
                    url = 'admin/user-single-player-report-filter';
                    clas = 'balnce';
                    id = 'totalb';
                }
                
                if(isnum){
                    if(tmpCrnt > parseInt($("#"+id).text())){
                       $(this).val(pCrnt); 
                    }else{
                        filterByParam(url,'');
                    }
                }else{
                    $(this).val(pCrnt);
                }
            });
            var tp = '';
          
            
         
        });
       function filterByParam(url,etype){
           var clas = '';
            if(url.indexOf('user-multi-player-report-filter') !== -1){
                div_id = 'vtable';
                var curPage=$("#crnt").val();
                var totalPage=$("#total").text();
                clas = 'coins';
            }else if(url.indexOf('user-single-player-report-filter') !== -1){
                div_id = 'btable';
                var curPage=$("#crntb").val();
                var totalPage=$("#totalb").text();
                clas = 'balnce';
                if(etype != '')
                    etype.id = etype.getAttribute('data-type');
            }
           
            var data = {user_id:user_id};
            

            if(curPage==totalPage && etype.id =='prev'){
                $("."+clas+" #next").removeClass('disabled');
                $("."+clas+" #last").removeClass('disabled');
            }
            else if(curPage==totalPage || etype.id=='last'){
                $("."+clas+" #next").addClass('disabled');
                $("."+clas+" #last").addClass('disabled');
            }else{
                $("."+clas+" #next").removeClass('disabled');
                $("."+clas+" #last").removeClass('disabled');
            }

            if(curPage==1 && etype.id=='next'){
                $("."+clas+" #first").removeClass('disabled');
                $("."+clas+" #prev").removeClass('disabled');
            }
            else if(curPage==1 && etype.id!='last' || etype.id=='first'){
                $("."+clas+" #first").addClass('disabled');
                $("."+clas+" #prev").addClass('disabled');    
                
                $("."+clas+" #next").removeClass('disabled');
                $("."+clas+" #last").removeClass('disabled');
            }
            else{
                $("."+clas+" #first").removeClass('disabled');
                $("."+clas+" #prev").removeClass('disabled');
            }

            filterDataWith(url,etype,data);
            div_id ='';
        }
    
        function filterByParamLen(url,len){
            var clas = '';
                if(url.indexOf('user-multi-player-report-filter') !== -1){
                    div_id = 'vtable';
                    var curPage=$("#crnt").val();
                    var totalPage=$("#total").text();
                    clas = 'coins';
                }else if(url.indexOf('user-single-player-report-filter') !== -1){
                    div_id = 'btable';
                    var curPage=$("#crntb").val();
                    var totalPage=$("#totalb").text();
                    clas = 'balnce';
                }
                
                var data = {user_id:user_id};
                
                if(curPage<=totalPage ){
                    $("."+clas+" #next").removeClass('disabled');
                    $("."+clas+" #last").removeClass('disabled');
                }

                filterPageData(url,len,data);    
                div_id = '';
            }
     
    
   
    </script>

    <?php 
    
    $avtar = $user['avatar'];
   
    if($avtar == ''){
        $avtar = 'my_avatar.jpg';
    }
    
    $avtar_url = URL::to('assets/uploads/avatar').'/'.$avtar;
    $file_check = config('constant.USER_AVATAR_PATH').$avtar;
    if(!file_exists($file_check)){
        $avtar_url = URL::to('assets/images/').'/my_avatar.jpg';
    }
    ?> 
          <div class="box-inner padding">
            

<div class="row row-sm">
  <div class="col-sm-4">
    <div class="panel panel-card">
      <div class="r-t pos-rlt waves-effect" md-ink-ripple="" style="background:url('{{$avtar_url}}') center center; background-size:cover;width: 100%">
        <div class="p-lg bg-white-overlay text-center r-t">
          <a class="w-xs inline">
            <img src="{{$avtar_url}}" class="img-circle img-responsive">
          </a>
          <div class="m-b m-t-sm h2">
            <span class="">{{$user['username']}}</span>
          </div>
            <a class="btn btn-sm btn-success m-b p-h no-border waves-effect" id="edit_user_btn">Edit</a>
          <p>
           Email : {{$user['email']}}
          </p>
          <p>
           Date Of Year : {{$user['dob']}}
          </p>
       
        </div>
      </div>
      <div class="list-group no-radius no-border">
          <a class="list-group-item" id="mp_report">
              <span id="c_count" class="pull-right badge">{{$body['total_mp']}}</span> Multi Player Report
        </a>
          <a class="list-group-item" id="sp_report">
          <span id="b_count" class="pull-right badge">{{$body['total_sp']}}</span> Single Player Report
        </a>
         
          <?php
          $status = $user['status'];
          $ina = $status == 0 ? 'selected' : '';
          $act = $status == 1 ? 'selected' : '';
          $pen = $status == 2 ? 'selected' : '';
          $sus = $status == 3 ? 'selected' : '';
          ?>
        <a class="list-group-item">
            <span class="pull-right">
                <select id="user_status" class="md-input">
                    <option value="0" <?php echo $ina ?> >In Active</option>
                    <option value="1" <?php echo $act ?> >Active</option>
                    <option value="2" <?php echo $pen ?> >Pending</option>
                    <option value="3" <?php echo $sus ?> >Suspend</option>
                </select>
            </span>
            Status
        </a>

        <?php
          $status = $user['robot_status'];
          $checked = $status == 1 ? 'checked' : '';
          ?>
        <a class="list-group-item">
            <span class="pull-right">
              <p>
                <label class="md-switch">
                  <input type="checkbox" name="robot_switch" id="robot_switch" {{$checked}} class="has-value">
                  <i class="green"></i>
                </label>
              </p>
            </span>
            Robot Status
        </a>
       
      </div>
        
    </div>
       <div class="panel panel-card">
     <div class="text-center b-b b-light">
        <div class="list-group-item text-primary deep-purple-500">
            <span style="color:white;">Single Player Stats</span>
        </div>
              <span style="float:left;margin-left:5px;color:#4caf50;line-height: 5; ">Average Score</span> 
        <a href="" class="inline m text-color">
        
          <span class="h3 block font-bold">{{$body['user_sp_stat']['avg_score']}}</span>
          <em class="text-xs">Total</em>
        </a>
        <a href="" class="inline m text-color">
          <span class="h3 block font-bold">{{$body['user_sp_stat']['avg_score_classic']}}</span>
          <em class="text-xs">Classic</em>
        </a>
             <a href="" class="inline m text-color">
          <span class="h3 block font-bold">{{$body['user_sp_stat']['avg_score_timed']}}</span>
          <em class="text-xs">Timed</em>
        </a>
      </div>
     
     <div class="text-center b-b b-light">
          <span style="float:left;margin-left:5px;color:#2196f3;line-height: 5; "> Highest Score  </span> 
        <a href="" class="inline m text-color">
          
          <span class="h3 block font-bold">{{$body['user_sp_stat']['high_score']}}</span>
          <em class="text-xs">Overall</em>
        </a>
        <a href="" class="inline m text-color">
          <span class="h3 block font-bold">{{$body['user_sp_stat']['high_score_classic']}}</span>
          <em class="text-xs">Classic</em>
        </a>
             <a href="" class="inline m text-color">
          <span class="h3 block font-bold">{{$body['user_sp_stat']['high_score_timed']}}</span>
          <em class="text-xs">Timed</em>
        </a>
     </div>   
     
     <div class="text-center b-b b-light">
            <span style="float:left;margin-left:5px;color:#7e57c2;line-height: 5; ">  Games Played   </span> 
        <a href="" class="inline m text-color">
          
          <span class="h3 block font-bold">{{$body['user_sp_stat']['total_played']}}</span>
          <em class="text-xs">Total</em>
        </a>
        <a href="" class="inline m text-color">
          <span class="h3 block font-bold">{{$body['user_sp_stat']['classic_played']}}</span>
          <em class="text-xs">Classic</em>
        </a>
             <a href="" class="inline m text-color">
          <span class="h3 block font-bold">{{$body['user_sp_stat']['timed_played']}}</span>
          <em class="text-xs">Timed</em>
        </a>
     </div>
        
     <div class="text-center b-b b-light">
        <span style="float:left;margin-left:5px; "></span> 
        <div class="list-group-item red-500">
           <span style="color:white;">Multi Player Stats</span> 
        </div>
         <a href="" class="inline m text-color">
          <span class="h3 block font-bold">{{$body['user_mp_stat']['played']}}</span>
          <em class="text-xs">Total Played</em>
        </a> 
        <a href="" class="inline m text-color">
          <span class="h3 block font-bold">{{$body['user_mp_stat']['total_score']}}</span>
          <em class="text-xs">Total Score</em>
        </a> 
        <a href="" class="inline m text-color">
           
          <span class="h3 block font-bold">{{$body['user_mp_stat']['won']}}</span>
          <em class="text-xs">Won</em>
        </a>
        <a href="" class="inline m text-color">
          <span class="h3 block font-bold">{{$body['user_mp_stat']['tie']}}</span>
          <em class="text-xs">Tie</em>
        </a>
             <a href="" class="inline m text-color">
          <span class="h3 block font-bold">{{$body['user_mp_stat']['lost']}}</span>
          <em class="text-xs">Lost</em>
        </a>
      </div>   
        
        
      
    </div>
  </div>
  <div class="col-sm-8">
    
      <div class="panel panel-card clearfix" id="mp_report_div">
      <div class="p-h b-b b-light">
        <ul class="nav nav-lines nav-md b-info">
            <li class="active" ><a href="#">Multi Player  Report</a></li>
        </ul>
      </div>
      <div class="p-h-lg m-b-lg">      
          
          <div class="row coins">
                        <div class="col-md-12" style="text-align:center;">

                                <div class="btn-group">
                                    <button type="button" id="first" class="btn btn-default waves-effect f1" onclick="filterByParam('admin/user-multi-player-report-filter',this);"><i class="fa fa-angle-double-left" aria-hidden="true"></i></button>
                                    <button type="button" id="prev" class="btn btn-default waves-effect p1" onclick="filterByParam('admin/user-multi-player-report-filter',this);"><i class="fa fa-angle-left" aria-hidden="true"></i></button>
                                    
                                </div>
                                <div class="btn-group m-l-sm ">
                                    <input type="text" class="form-control col col-xs-1 crnt" id="crnt" data-type='mp' value="1"  style="width:50px;height:28px;margin-top:5px;text-align: center;">
                                        <label class="m-l-sm"  style="font-size:20px;margin-top:5px;">/</label>
                                        <label  style="font-size:18px;"><span id="total"></span> page(s)</label>
                                </div>
                            
                                <div class="btn-group m-l-sm">
                                    
                                    <button type="button" id="next" class="btn btn-default  waves-effect n1" onclick="filterByParam('admin/user-multi-player-report-filter',this);"><i class="fa fa-angle-right" aria-hidden="true" ></i></button>
                                    <button type="button" id="last" class="btn btn-default waves-effect l1" onclick="filterByParam('admin/user-multi-player-report-filter',this);"><i class="fa fa-angle-double-right" aria-hidden="true"></i></button>
                                </div>
                                <div class="btn-group m-l-sm">
                                    <select class="form-control" id="len" style="height:28px;" onchange="filterByParamLen('admin/user-multi-player-report-filter',this.value);">
                                    <option value="20">20</option>
                                    <option value="30">30</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                </div>
                             <hr/>
                          
                                </div>
                                
                   
                    </div>
                    <div class="table-responsive">
                    <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper form-inline dt-bootstrap no-footer">
                        <div class="row">
                            <div class="col-sm-12">

                                <div id='vtable'>
            
                                </div>
                                 <hr/>
                            
                            </div>
                        </div>
                        </div>
                    </div>
          
      </div>
    </div>
    
    <div class="panel panel-card clearfix" id="sp_report_div" style="display: none">
      <div class="p-h b-b b-light">
        <ul class="nav nav-lines nav-md b-info">
            <li class="active" ><a href="#">Single Player Report</a></li>
        </ul>
      </div>
      <div class="p-h-lg m-b-lg">      
            <div class="row balnce">
                        <div class="col-md-12" style="text-align:center;">

                                <div class="btn-group">
                                    <button type="button" id="firstb" data-type='first' class=" btn btn-default waves-effect f1" onclick="filterByParam('admin/user-single-player-report-filter',this);"><i class="fa fa-angle-double-left" aria-hidden="true"></i></button>
                                    <button type="button" id="prevb" data-type='prev' class=" btn btn-default waves-effect p1" onclick="filterByParam('admin/user-single-player-report-filter',this);"><i class="fa fa-angle-left" aria-hidden="true"></i></button>
                                    
                                </div>
                                <div class="btn-group m-l-sm ">
                                    <input type="text" class="form-control col col-xs-1 crnt" id="crntb" data-type='sp' value="1"  style="width:50px;height:28px;margin-top:5px;text-align: center;" >
                                        <label class="m-l-sm"  style="font-size:20px;margin-top:5px;">/</label>
                                        <label  style="font-size:18px;"><span id="totalb"></span> page(s)</label>
                                </div>
                            
                                <div class="btn-group m-l-sm">
                                    
                                    <button type="button" id="nextb" data-type='next' class=" btn btn-default  waves-effect n1" onclick="filterByParam('admin/user-single-player-report-filter',this);"><i class="fa fa-angle-right" aria-hidden="true" ></i></button>
                                    <button type="button" id="lastb" data-type='last' class=" btn btn-default waves-effect l1" onclick="filterByParam('admin/user-single-player-report-filter',this);"><i class="fa fa-angle-double-right" aria-hidden="true"></i></button>
                                </div>
                                <div class="btn-group m-l-sm">
                                    <select class="form-control" id="lenb" style="height:28px;" onchange="filterByParamLen('admin/user-single-player-report-filter',this.value);">
                                    <option value="20">20</option>
                                    <option value="30">30</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                </div>
                              <hr/>
                        
                                </div>
                                
                  
                    </div>
                    <div class="table-responsive">
                    <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper form-inline dt-bootstrap no-footer">
                        <div class="row">
                            <div class="col-sm-12">

                                <div id='btable'>
                                </div>
                                 <hr/>

                            </div>
                        </div>
                        </div>
                    </div>
      </div>
    </div>
      

    <div class="panel panel-card clearfix" id='edit_user_div' style="display: none">
      <div class="p-h b-b b-light">
        <ul class="nav nav-lines nav-md b-info">
          <li class="active"><a href="#">Edit User</a></li>
        </ul>
      </div>
      <div class="p-h-lg m-b-lg">      
          <form id="updateInfo" role='form' action="{{URL::to('admin/edit-user-detail')}}"  method="post">
        
                <div class="box-row">
                    
                             
                            <div class="col-xs-12 m-t-10 ">
                                <div class="md-form-group float-label">
                                        <input class="md-input" type="text" id="user_name" name="user_name" value="{{$body['user']['username']}}" placeholder="Name" required="">
                                    <input class="md-input" type="hidden" id="token" name="_token" value="{{csrf_token()}}" >
                                    <input class="md-input" type="hidden" id="user_id" name="user_id" value="{{$body['user']['id']}}" >
                                    <!--<label>Name</label>-->
                                </div>
                            </div>  
                            <div class="col-xs-12 m-t-10 ">
                                <div class="md-form-group float-label">
                                    <input class="md-input" type="text" id="user_email" name="user_email" value="{{$body['user']['email']}}" placeholder="Email" required="" readonly="">
                                        <!--<label>Email</label>-->
                                </div>
                            </div>
                            
                            <div class="col-xs-12 m-t-10 ">
                                <div class="md-form-group float-label input-group m-b" style="vertical-align: central;">
                                    <span class="" style="font-size: 18px;"><i class="fa fa-mobile" aria-hidden="true" style="font-size: 20px; "></i>  {{$body['user']['dob']}}</span>
                                    
                                    <!--<input class="form-control" type="text" id="user_mobileno" name="user_mobileno" value="{{$body['user']['dob']}}" style="padding-left: 10px;" placeholder="Mobile No." readonly>-->
                                </div>
                            </div> 
                        
                </div>
            
            <div class="modal-footer no-border">
                <span class="alert-msg success" style="color:green;display: none;"></span>
                <span class="alert-msg failed" style="color:red;display: none;"></span>
                <button type="button" class="btn btn-primary waves-effect" id="edit_user">Save Changes</button>
                
            </div>
       
        </form>
      </div>
    </div>  
  </div>
</div>
</div>
        
   

   
@endsection