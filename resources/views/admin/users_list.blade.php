@extends('layouts.admin')
@section('content')
@section('header')
<style type="text/css">
    .modal-text{
        padding: 24px;
    }
    .modal-text h2{
        margin-top:0px; 
    }
    .modal-backdrop {
          position: fixed;
          top: 0;
          right: 0;
          bottom: 0;
          left: 0;
          background-color:rgba(0,0,0,.4);
        }
    @media (min-width: 768px){
        .modal-sm {
            width:360px;
        }
        .modal-dialog {
            margin: 18% auto;
        } 
    }
</style>
@stop
    <div class="panel panel-default">
        <div class="panel-heading"> <b>Users List</b></div>
        <div class="panel-body">
            
            <div class="panel-group" id="accordion">
                <div class="panel panel-default">
                    <div class="panel-heading" style="height: 45px;">
                    <div class="panel-title">
                        <span class="col-sm-10 col-lg-11 col-md-11" style="margin-top: -5px;">Fliter  </span>
                        <span class="col-sm-2 col-lg-1 col-md-1">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapse1" style="float: right;"><button style="margin-top: -10px;" class="btn btn-icon btn-default btn-icon-sm md-ink-ripple"><i class=" fa-filter fa editable i-16"></i></button></a>
                        </span>
                    </div>
                  </div>
                  <div id="collapse1" class="panel-collapse collapse ">
                    <div class="panel-body">
                        <div class="col-md-2 md-form-group float-label m-t-md mrg-top">
                            <input class="md-input" id="name_mobile" required="">
                            <label>Name </label>
                               <input type="hidden" id="user_id_search" />
                        </div>
                        <div class="col-md-2 md-form-group float-label m-t-md mrg-top m-l-sm">
                            <input class="md-input" id="email_search" required="">
                            <label>Email</label>
                        </div>
                        <div class="col-md-2 md-form-group  m-t-md mrg-top m-l-sm">
                            
                            <select class="md-input" id="status_search" aria-invalid="false">
                                <option value="" selected="">All</option>
                                <option value="0">In Active</option>       
                                <option value="1">Active</option>       
                                <option value="2">Pending</option>       
                                <option value="3">Suspended</option>       
                            </select>
                            <label>Status</label>
                        </div>
                        <div class="col-md-2 md-form-group  m-t-md mrg-top m-l-sm">
                            
                            <select class="md-input" id="robot_status_search" aria-invalid="false">
                                <option value="" selected="">All</option>
                                <option value="1">Active</option>       
                                <option value="0">Deactive</option>       
                            </select>
                            <label>Robot Status</label>
                        </div>
                        <div class="col-md-2 md-form-group float-label m-t-md mrg-top">
                            <button type="button" class="btn btn-default btn-sm waves-effect " id="search_reset" tabindex="0">Reset</button>
                            <button type="button" class="btn  btn-sm md-raised indigo waves-effect " id="search_option" tabindex="0">Search</button>
                        </div>
                    </div>
                  </div>
                </div>
            </div> 
            
            <div class="row">
                <div class="col-lg-9 colmd-9">
                    <ul class="pagination" style="margin: 0px">
                        <li><a href="#">First</a></li>
                        <li><a href="#">Previous</a></li>
                        <li><a href="#">1</a></li>
                        <li><a href="#">Next</a></li>
                        <li><a href="#">Last</a></li>
                    </ul>
                    <!--<button type="button" class="btn btn-sm md-raised indigo waves-effect " id="opanModel" data-toggle="modal" data-target="#credit_debit" style="margin: 0px;float: right;" tabindex="0">Change User</button>-->
                </div>
                <div class="col-lg-3 colmd-3">
                    <span>Record per page : </span>
                    <input type="text" id="recordPerPage"  value=""/>
                    <button type="button" class="btn md-raised btn-sm indigo waves-effect" onclick="changeRecordPerPage('{{URL::to("admin/user-filter")}}');" ><i class="mdi-av-replay editable i-16"></i>
                            </button>
                </div>
            </div>
            <br>
            <div id='vtable'>
            
            </div>
        </div>
    </div>

<!-- delete all record -->
<div class="modal fade bs-example-modal-sm" tabindex="-1" id="delete-form-modal" role="dialog" aria-labelledby="mySmallModalLabel">
  <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
    <form id="delete-form" role='form' action="{{url('admin/delete-user')}}"  method="post">
        <input class="md-input" type="hidden" id="token" name="_token" value="{{csrf_token()}}" >
        <input class="hidden" type="hidden" id="delete_id" name="delete_id" value="" >
        <div class="modal-content">
            <div class="modal-text">
                <h2 class="md-title ng-binding">Are you sure you want to delete ?</h2>
                <div class="md-dialog-content-body " style="">
                    <p class="ng-binding">Warning: This record will no longer available</p>
                </div>
            </div>
            <div class="text-right">
                <button class="md-primary md-button md-default-theme md-ink-ripple" type="button" id="closeModelD"   data-dismiss="modal" tabindex="0" aria-label="Cancel" style=""><span class="ng-binding ng-scope">Cancel</span><div class="md-ripple-container" style=""></div></button>

                <button class="md-primary md-button md-default-theme md-ink-ripple" type="button" onclick="deleteUser()" tabindex="0" aria-label="OK"><span class="">Delete</span><div class="md-ripple-container" style=""></div></button>
            </div>
        </div>
    </form>
  </div>
</div>
<!-- delete all record -->
@endsection
@section('footer')
<script>
        var url = '{{URL::to("admin/user-filter")}}';
        window.onload=function(){
            $("#<?php echo $body['id']?>").addClass("active");
//            var crnt = $('#crnt').val();
//            var len = $('#len').val();
//            var type = $('#type').html();
//            $('#cntrlbtn').css('display','none');
            
            filterData(url);
            
        };
        $(document).ready(function(){
            $('#search_option').on('click',function(){
                var name = $('#name_mobile').val();
                var email = $('#email_search').val();
                var status = $('#status_search :selected').val();
                var robot_status = $('#robot_status_search :selected').val();
                var user_id = $('#user_id_search').val();
                filters.user_id = user_id;
                filters.name = name;
                filters.email = email;
                filters.status = status;
                filters.robot_status = robot_status;
                filters.currentPage = 1;
                filterData(url);
            });
            $('#search_reset').on('click',function(){
                $('#name_mobile').val('');
                $('#email_search').val('');
                $('#status_search').val('');
                $('#robot_status_search').val('');
                $('#name_mobile').trigger('blur');
                $('#email_search').trigger('blur');
                $('#user_id_search').val('');
                filters.name = '';
                filters.email = '';
                filters.status = '';
                filters.robot_status = '';
                filters.user_id = '';
                filters.currentPage = 1;
                filterData(url);
            });
            
            var flag = 0;
            $(document).on('keyup','#name_mobile',function(){
                var s = $(this).val();
                if(s == ''){
                    $("#user_id_search").val('');
                }
                if(s.length % 2==0){
                    flag = 1;
                }
                if(flag == 1){
                    flag = 0;
                   var urlu = '{{URL::to("admin/user-name")}}';
                   var data = {name:s,is_mobile:1};
                   postAjax(urlu,data,function(res){
                       
                       if(res.flag == 1){
                           $( "#name_mobile" ).autocomplete({
                            source: res.data,
                            focus: function( event, ui ) {
                                $("#user_id_search").val(ui.item.key);
                //                $("#user_id").val(ui.item.key);
                                return false;
                                },
                          });
                       }
                   });
                }
            });   
      
        });
        function deleteRowData(id) {

            $('#delete_id').val(id);
            $("#delete_btn").prop('disabled', false);
            $('#delete-form-modal').modal('show');
        }

        function deleteUser() {
            $('#delete-form').ajaxForm(function(res) {
                if (res.flag != 1) {
                    Toast(res.msg, 3000, res.flag);
                } else {
                    $('#closeModelD').trigger('click');
                    Toast(res.msg, 3000, res.flag);
                    filterData(url);
                }
            }).submit();
        }
                
        
    </script>
@stop