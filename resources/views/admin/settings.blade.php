@extends('layouts.admin')
@section('content')
<?php
$settings = app('settings');
?>
    <script>
        
        window.onload=function(){
            $("#<?php echo $body['id']?>").addClass("active");
        };
        
        function randomString(length) {
            var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                length = length || 10;
                var string = '', rnd;
                while (length > 0) {
                        rnd = Math.floor(Math.random() * chars.length);
                        string += chars.charAt(rnd);
                        length--;
                }
                return string;
        }
        
        function setRequestToken(){
            var token = randomString(20);
            $('#request_token').val(token);
//            console.log(token);
        }
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();  
            
            $('#general_save').click(function(){
                $('#g_setting').ajaxForm(function(res){
                    
                    if(res.flag == 1){
                        Toast(res.msg);
                        $('.general-msg').html('');
                    }else{
                        $('.general-msg').html(res.msg);
                    }
                }).submit();
            });
            $('#bitpay_save').click(function(){
                $('#bit_setting').ajaxForm(function(res){
                    if(res.flag == 1){
                        Toast(res.msg);
                        $('.bitpay-msg').html('');
                    }else{
                        $('.bitpay-msg').html(res.msg);
                    }
                }).submit();
            });
            $('#password_save').click(function(){
                $('#c_password').ajaxForm(function(res){
                    if(res.flag == 1){
                        Toast(res.msg);
                        $('.pwd-msg').html('');
                    }else{
                        $('.pwd-msg').html(res.msg);
                    }
                    $('#old_password').val('');
                    $('#new_password').val('');
                    $('#confirm_password').val('');
                }).submit();
            });
            $('#pwd_cancel').click(function(){
                $('#old_password').val('');
                $('#new_password').val('');
                $('#confirm_password').val('');
                $('.pwd-msg').html('');
            });
        });
        
    </script>

   
    <div class="row default_margin">   
        
    <div class="panel panel-card clearfix">
        <div class="b-b b-light">
          <ul class="nav nav-lines nav-md b-info nav-tabs">
              <li class="active" ><a data-toggle="tab" href="#general">GENERAL</a></li>

              <li ><a data-toggle="tab" href="#change_pwd">CHANGE PASSWORD</a></li>
          </ul>
        </div>
        <div class="p-h-lg m-b-lg">      
            <div class="tab-content">
                
                <div id="general" class="tab-pane fade  in active">
                    <div class="box-row">
                        <form method="post" id="g_setting" action="{{URL::to('admin/save-settings')}}" >
                            {{csrf_field()}}
                            <input type="hidden" name="settting_type" value="general" />
                    <div>
                        <div class="row card-body" style="width: 100%">
                            
                            
                            <div class="col-xs-12 m-t-10 bg-white no-padding pad_t_b">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Login Url Token</label>
                                    <div class="col-sm-7 m-b-10">
                                        <input type="text" class="form-control" name="login_url_token" placeholder="Enter login url token"  value="{{$settings['login_url_token']}}" tabindex="0" aria-invalid="false" >
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 m-t-10 bg-white no-padding pad_t_b">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Time Master time for Single User</label>
                                    <div class="col-sm-7 m-b-10">
                                        <input type="text" class="form-control" name="time_master_single_player_time" placeholder="Enter Time master time of single user"  value="{{$settings['time_master_single_player_time']}}" tabindex="0" aria-invalid="false" >
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 m-t-10 bg-white no-padding pad_t_b">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Time Master time for Multiple User</label>
                                    <div class="col-sm-7 m-b-10">
                                        <input type="text" class="form-control" name="time_master_multi_player_time" placeholder="Enter Time master time of multiple user"  value="{{$settings['time_master_multi_player_time']}}" tabindex="0" aria-invalid="false" >
                                    </div>
                                </div>
                            </div>
                             <div class="col-xs-12 m-t-10 bg-white no-padding pad_t_b">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Multiplayer game classic expire</label>
                                    <div class="col-sm-7 m-b-10">
                                        <input type="text" class="form-control" name="login_url_token" placeholder="Enter login url token"  value="{{$settings['multi_player_game_classic_expire']}}" tabindex="0" aria-invalid="false" readonly="readonly">
                                    </div>
                                </div>
                            </div>
                      
                            <div class="col-xs-12 m-t-10 bg-white no-padding pad_t_b">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Multiplayer game timed expire</label>
                                    <div class="col-sm-7 m-b-10">
                                        <input type="text" class="form-control" name="conversion_rate" placeholder="Enter conversation rate" value="{{$settings['multi_player_game_timed_expire']}}" tabindex="0" aria-invalid="false" readonly="readonly">
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 m-t-10 bg-white no-padding pad_t_b">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Total game per challenge</label>
                                    <div class="col-sm-7 m-b-10">
                                        <input type="text" class="form-control" name="ico_refer" placeholder="Enter ico refer" value="{{$settings['total_game_per_challenge']}}" tabindex="0" aria-invalid="false" readonly="readonly">
                                    </div>
                                </div>
                            </div>
                            
                                <div class="col-xs-12 m-t-10 bg-white no-padding pad_t_b" style="display:none;">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Match Number Range</label>
                                    <div class="col-sm-7 m-b-10">
                                        <input type="text" class="form-control" name="ico_refer" placeholder="Enter ico refer" value="{{$settings['match_num_range']}}" tabindex="0" aria-invalid="false" readonly="readonly">
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 m-t-10 bg-white no-padding pad_t_b">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Per hint reduce</label>
                                    <div class="col-sm-7 m-b-10">
                                        <input type="text" class="form-control" name="refer_reward" placeholder="Enter refer reward" value="{{$settings['per_hint_reduce']}}" tabindex="0" aria-invalid="false" readonly="readonly">
                                    </div>
                                </div>
                            </div>

                            <div class="col-xs-12 m-t-10 bg-white no-padding pad_t_b" style="display: none;">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"> Hint Range</label>
                                    <div class="col-sm-7 m-b-10">
                                        <input type="text" class="form-control" name="refer_reward" placeholder="Enter refer reward" value="{{$settings['hint_range']}}" tabindex="0" aria-invalid="false" readonly="readonly">
                                    </div>
                                </div>
                            </div>
<!--                            <div class="col-xs-12 m-t-10 bg-white no-padding pad_t_b">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Maintenance Mode</label>
                                    <div class="col-sm-7 m-b-10">
                                        <div class="input-group">
                                            <label class="md-switch">
                                                <input type="checkbox" name="maintanance_mode" class="" tabindex="0" <?php // echo $settings[4]['val'] == 1 ? 'checked' : '' ?> aria-checked="false" aria-invalid="false">
                                                <i class="green"></i>   
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>-->
                            <div class="col-xs-12 m-t-10 bg-white no-padding pad_t_b">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"></label>
                                    <div class="col-sm-7 m-b-10">
                                        <div class="modal-footer no-border">
                                        <span class="text-danger general-msg" aria-hidden="true"></span>
                                        <a class="btn btn-primary" id="general_save"  aria-label="Save" tabindex="0">Save</a>
                                        <a class="btn btn-default" tabindex="0">Cancel</a>
                                        
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                        </form>
                </div>
                </div>
                

                
                <div id="change_pwd" class="tab-pane fade ">
                    <div class="box-row">
                        <form method="post" id="c_password" action="{{URL::to('admin/save-settings')}}" >
                            {{csrf_field()}}
                            <input type="hidden" name="settting_type" value="password" />
                    <div>
                        <div class="row card-body">
                            <div class="col-xs-12 m-t-10 bg-white no-padding pad_t_b">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label redio-label">Old Password</label>
                                    <div class="col-sm-7 m-b-10">
                                        <input type="password" class="form-control" id="old_password" name="old_password" placeholder="Enter Old Password" tabindex="0" aria-invalid="false" style="">
                                    </div>
                                </div>
                            </div>  
                            <div class="col-xs-12 m-t-10 bg-white no-padding pad_t_b">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label redio-label">New Password</label>
                                    <div class="col-sm-7 m-b-10">
                                        <input type="password" class="form-control " id="new_password" name="new_password" placeholder="Enter New Password" tabindex="0" aria-invalid="false" style="">
                                    </div>
                                </div>
                            </div> 
                            <div class="col-xs-12 m-t-10 bg-white no-padding pad_t_b">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label redio-label">Confirm Password</label>
                                    <div class="col-sm-7 m-b-10">
                                        <input type="password" class="form-control " id="confirm_password" name="confirm_password" placeholder="Enter Confirm Password" tabindex="0" aria-invalid="false" style="">
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 m-t-10 bg-white no-padding">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"></label>
                                    <div class="col-sm-7 m-b-10">
                                        <div class="modal-footer no-border pull-right">
                                        <span class="text-danger pwd-msg" aria-hidden="true"></span>
                                        <a class="btn btn-primary" id="password_save" aria-label="Save" tabindex="0">Change Password</a>
                                        <a class="btn btn-default" id="pwd_cancel" tabindex="0">Cancel</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                        </form>
                </div>
                </div>
            </div>



        </div>
    </div>
            
    </div>  
    

@endsection