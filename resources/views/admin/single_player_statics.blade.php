@extends('layouts.admin')
@section('content')
    <script>
        var url = '{{URL::to("admin/single-player-statics-filter")}}';
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
                var user_id = $('#user_id_search').val();
                var start_date = $('#startd').val();
                var end_date = $('#endd').val();
                   var status = $('#status_search :selected').val();
                filters.user_id = user_id;
                filters.start_date = start_date;
                filters.end_date = end_date;
                filters.status = status;
                filters.currentPage = 1;
                filterData(url);
            });
            $('#search_reset').on('click',function(){
                $('#name_mobile').val('');
                $('#user_id_search').val('');
                $('#startd').val('');
                $('#endd').val('');
                $('#name_mobile').trigger('blur');
                $('#status_search').val("");
                $('#datepicker').val('{{date("Y-m-d")." - ".date("Y-m-d")}}');
                filters.user_id = '';
                filters.start_date = '';
                filters.end_date = '';
                 filters.status = '';
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
            
            
            
            $('#savaInfo').click(function(){
                var user_id = $('#user_id').val();
                var amount = $('#amount').val();
                if(user_id == ''){
                    Toast('Please select valid user');
                    return false;
                }
                
                if(amount == '' || !isNumeric(amount)){
                    Toast('Please enter valid amount');
                    return false;
                }
                
                $('#form_credit').ajaxForm(function(res){
                    if(res.flag != 1){
                        Toast(res.msg);
                    }else{
                        $('#closeModel').trigger('click');
                        Toast(res.msg);
                        filterData(url);
                        
                        
                    }
                }).submit();
            });
            
            $('#opanModel').click(function(){
                $('#user_name').val('');
                $('#amount').val('');
                $('#notes').val('');
            });
            
             $("#datepicker").daterangepicker(
            {
              locale: {
                format: 'YYYY-MM-DD'
              },
            }, 
           function(start, end, label) {

                $("#startd").val(start.format('YYYY-MM-DD'));
                $("#endd").val(end.format('YYYY-MM-DD'));
           }       
           );
        });
        
        
    </script>

    <div class="panel panel-default">
        <div class="panel-heading"> <b> Report</b></div>
        <div class="panel-body">
            
            <div class="panel-group" id="accordion">
                <div class="panel panel-default">
                    <div class="panel-heading" style="height: 45px;">
                    <div class="panel-title">
                        <span class="col-sm-10 col-lg-11 col-md-11" style="margin-top: -5px;">Filter  </span>
                        <span class="col-sm-2 col-lg-1 col-md-1">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapse1" style="float: right;"><button style="margin-top: -10px;" class="btn btn-icon btn-default btn-icon-sm md-ink-ripple"><i class=" fa-filter fa editable i-16"></i></button></a>
                        </span>
                    </div>
                  </div>
                  <div id="collapse1" class="panel-collapse collapse ">
                    <div class="panel-body">
                        <div class="col-md-2 md-form-group float-label m-t-md mrg-top">
                            <input class="md-input" id="name_mobile" required="">
                            <label>User name</label>
                            <input type="hidden" id="user_id_search" />
                        </div>
<!--                        <div class="col-md-2 md-form-group   m-t-md mrg-top m-l-sm m-r-sm">
                            
                            <select class="md-input" id="status_search" aria-invalid="false">
                                <option value="" selected="">All</option>
                                <option value="0">Lost</option>       
                                <option value="1">Won</option>       
                                <option value="2">Tie</option> 
                                      
                            </select>
                            <label>Type</label>
                        </div>-->
                        <div class="col-md-2 md-form-group  m-t-md mrg-top m-l-sm">
                            <input class="md-input" id="datepicker" required="">
                            <label>Select Date</label>
                            <input type="hidden" id="startd" />
                            <input type="hidden" id="endd" />
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
               
                </div>
                <div class="col-lg-3 colmd-3">
                    <span>Record per page : </span>
                    <input type="text" id="recordPerPage"  value=""/>
                    <button type="button" class="btn md-raised btn-sm indigo waves-effect" onclick="changeRecordPerPage('{{URL::to("admin/single-player-statics-filter")}}');" ><i class="mdi-av-replay editable i-16"></i>
                            </button>
                </div>
            </div>
            <br>
            <div id='vtable'>
            
            </div>
        </div>
    </div>


@endsection