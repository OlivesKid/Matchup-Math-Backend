/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* ---- Common Ajax structure ----.

var url=getBaseURL()+"payment/coupon-discount";
var data={ccode:code,busid:b_id,amount:amount};
postAjax(url,data,function(res){
    console.log(res);
});

*/
var filter_url = '';
var filters = {
    totalItems: 0,
    itemPerPage: 10,
    currentPage: 1,
    totalPages: 1,
};

var div_id = '';

$(document).ready(function(){
    $('#recordPerPage').val(filters.itemPerPage);
    $('#status_search').trigger('focus');
    
    $(document).on('click','.page_no',function(){
        var cp = $(this).data('page');
        
        filters.currentPage = cp;
        filterData(filter_url);
    });
    
    $(document).on('keydown','.ui-autocomplete-input',function(e){
        var k = e.which;
        
        if(k == 40 || k == 38){
            $('.ui-autocomplete .ui-menu-item').removeClass('custom-autocomplete-hover');
            $('.ui-autocomplete li a').each(function(){
                
                if($(this).hasClass('ui-state-focus')){
                    console.log($(this));
                    $(this).parent('li').addClass('custom-autocomplete-hover');
                }
            });
//            console.log($('.ui-autocomplete li').find('a .ui-state-focus').html());
            
//            $('.ui-autocomplete li').find('a .ui-state-focus').parent('li').addClass('custom-autocomplete-hover');
        }
    });
});

function changeRecordPerPage(url){
    var recPp = $('#recordPerPage').val();
    if(recPp == '' || recPp <= 0){
        Toast('Please select valid page limit');
        return false;
    }
    filters.currentPage = 1;
    filters.itemPerPage = recPp;
    
    filterData(url);
}

function filterData(url){
    var token = $("#token").val();
    filters._token=token;
    var jdata = filters;
    $.ajax({
        type:'POST',
        url:url,
        data:jdata,
        success: function(res){
            if(res.flag === 0){
                console.log(res);
            }else{
                $("#vtable").html(res.blade);
                filters.totalItems = res['total_record'];
                filters.totalPages = filters.totalItems > 0 ? Math.ceil(filters.totalItems / filters.itemPerPage) : 0;
                setPagination();
            }
        }, 
    });
    filter_url = url;
}

function setPagination(){
    var tp = filters.totalPages;
    var cp = filters.currentPage;
    
    var p = prevPage(cp,tp,0);
    var li = '';
    var fl = '<li class="page_no" data-page="'+1+'" data-type="f"><a href="#">First</a></li>';
    var ll = '<li class="page_no" data-page="'+tp+'" data-type="l" ><a href="#">Last</a></li>';
    var pp = '<li class="page_no" data-page="'+p+'" data-type="p"><a href="#">Previous</a></li>';
    var p = prevPage(cp,tp,1);
    var np = '<li class="page_no" data-page="'+p+'" data-type="n"><a href="#">Next</a></li>';
    var ns = '';
    var ps = '';
    var prev = cp - 7;
    var next = cp + 7;
    var pflag = 1;
    var nflag = 1;
    if(prev < 0){
        pflag = 0;
    }
    if(next > tp){
        pflag = 0;
    }
    if(tp < 7){
        for(i = 1;i<=tp;i++){
            li += '<li class="page_no" data-page="'+i+'" data-type="'+i+'"><a href="#">'+i+'</a></li>';
        }
    }else{
        var nd = nextDigit(cp,tp,1);
        var dp = nextDigit(cp,tp,0);
        if(nd<tp){
          ns = '<li class="page_no" data-page="'+(nd+1)+'" data-type="'+(nd+1)+'"><a href="#">...</a></li>';
        }
        
        if(dp == nd){
            dp = (nd-7) > 0 ? (nd-7) : 1;
        }
//        console.log(dp + ' - ' + nd);
        if(dp>1){
            ps = '<li class="page_no" data-page="'+(dp-1)+'" data-type="'+(dp-1)+'"><a href="#">...</a></li>';
        }
        
        
        
        for(i=dp;i<=nd;i++){
            li += '<li class="page_no" data-page="'+i+'" data-type="'+i+'"><a href="#">'+i+'</a></li>';
        }
    }
    
    li = fl+pp+ps+li+ns+np+ll;
    $('.pagination').html(li);

    $('.page_no').each(function(){
        var tp = $(this).data('type');
        if(tp == cp){
            $(this).addClass('active');
        }
    });
    
    if(cp == 1){
        $('.pagination li:first-child').removeClass('page_no').addClass('pagination-disable');
        $('.pagination li:nth-child(2)').removeClass('page_no').addClass('pagination-disable');
    }
    if(cp == tp){
        $('.pagination li:last-child').removeClass('page_no').addClass('pagination-disable');
        $('.pagination li:nth-last-child(2)').removeClass('page_no').addClass('pagination-disable');
    }
    
    
}
function prevPage(cp,tp,t){
    var p = 1;
    if(t){
        p = cp + 1 < tp ? cp + 1 : tp > 0 ? tp : 1;
    }else{
        p = cp - 1 > 0 ? cp - 1 : 1;
    }
    
    return p;
}

function nextDigit(cp,tp,t){
    if(t){
        for(i=cp;i<=tp;i++){
            if(i%7 == 0){
                return i;
            }
        }
        return tp;
    }else{
        for(i=cp;i>0;i--){
            if(i%7 == 0){
                return i;
            }
        }
        return 1;
    }
    
}

function Toast(val,time) {
    $('body').append('<div id="toast"></div>');
    $('#toast').html(val);
    var x = document.getElementById("toast");
    
    x.className = "show";
    if(typeof time == 'undefined' || time == null){
        time = 3000;
    }

    setTimeout(function(){ x.className = x.className.replace("show", "");$('#toast').remove();time == null;}, time);
}

function getBaseURL(){
    var url = $("#base_url").val();
    return url;
}

function postAjax(url,data,cb){
    var token = $("#token").val();
    var jdata = {_token:token};
    
    for(var k in data){
        jdata[k]=data[k];
    }
    
    $.ajax({
        type:'POST',
        url:url,
        data:jdata,
        success: function(data){
            if(typeof(data)==='object'){
                if(data.flag==8){
                    window.location.replace("{{URL::to('login')}}");
                }
                cb(data);
            }
            else{
                cb(data);
            }
        }, 
    });
}

function getAjax(url,data,cb){
    var token = $("#token").val();
    var jdata = {_token:token};
    
    for(var k in data){
        jdata[k]=data[k];
    }
    
    $.ajax({
        type:'GET',
        url:url,
        data:jdata,
        dataType:'JSON',
        success: function(data){
            if(typeof(data)==='object'){
                if(data.flag==8){
                    window.location.replace("{{URL::to('login')}}");
                }
                cb(data);
            }
            else{
                cb(data);
            }
        }, 
    });
}




function pad(d) {
    return (d < 10) ? '0' + d.toString() : d.toString();
}



function getData(urlto,crnt,len,type,opr,search,extra){
    var url=getBaseURL()+urlto;
    var data={crnt:crnt,len:len,type:type,opr:opr,search:search};
    if(typeof extra !=='undefined'){
        for(var i in extra){
            data[i] = extra[i];
        }
    }
    var dv_id = div_id != '' ? div_id : 'vtable';
    postAjax(url,data,function(res){
        if(res.flag==0){
            
            $('#alerterror').html('<h3 style="color:red;">No Data Found !!</h3>');
            $('#alerterror').css('display','block');
            $('#'+dv_id).css('visibility','hidden');
            $('#cntrlbtn').css('display','none');
            Materialize.toast(res.msg+' No Change Occured.', 2000,'rounded')
            return false;
        }
        else{
            $("#"+dv_id).html(res);
            $('#alerterror').css('display','none');
            $('#'+dv_id).css('visibility','visible');
            $('#cntrlbtn').css('display','block');
            
            if(dv_id == 'btable'){
                $("#totalb").html($("#total_page-sp").val());
                $("#crntb").val($("#current-sp").val());
                $("#lenb").val($("#len-sp").val());
                $('#crntb').attr('max',$("#total_page-sp").val());
                $('#crntb').attr('min',1);
            }else{
                $("#crnt").val($("#current").val());
                $("#crnt2").val($("#current").val());
                $('#crnt').attr('max',$("#total_page").val());
                $('#crnt').attr('min',1);
                $("#len").val($("#len").val());
                $("#total").html($("#total_page").val());
                $("#total2").html($("#total_page").val());
            }
            
            
            if($("#total_page").val()==0){
              $("#nodata").hide();
              $("#n-found-msg").show();  
            }else{
                 $("#n-found-msg").hide();  
                $("#nodata").show();
            }
            
        }
    });

}



$(document).ready(function(){
    $('#changePass').on('click',function(){
        var new_pass = $('#new_pass').val();
        var cnew_pass = $('#cnew_pass').val();
        
        var url = getBaseURL()+"user/change-pass";
        var data={new_password : new_pass, confirm_password: cnew_pass};
        postAjax(url,data,function(res){
            if(res.flag != 1){
                $('#chnPasswordModal').find('.success').css('display','none');
                $('#chnPasswordModal').find('.failed').html(res.msg);
                $('#chnPasswordModal').find('.failed').css('display','block');
            }
            else{
                $('#chnPasswordModal').find('.failed').css('display','none');
                $('#chnPasswordModal').find('.success').html(res.msg);
                $('#chnPasswordModal').find('.success').css('display','block');
                $('#new_pass').val('');
                $('#cnew_pass').val('')
            }
        });
    });
    
   
    $('#savaInfo').on('click',function(){
//        alert('Hello');
        var url = getBaseURL() + "user/update-profile";
        
        $('#updateInfo').attr('action',url);
        $('#updateInfo').ajaxForm(function(res) {
            console.log(res);
            if(res.flag != 1){
                $('#editProfileModal').find('.success').css('display','none');
                $('#editProfileModal').find('.failed').html(res.msg);
                $('#editProfileModal').find('.failed').css('display','block');
                return false;
            }
            else{
                $('#editProfileModal').find('.failed').css('display','none');
                $('#editProfileModal').find('.success').html('Your Information Updated successfully !!');
                $('#editProfileModal').find('.success').css('display','block');
                $('#user_pic').attr('src',res.data.avatar);
            }
        }).submit();
//        });
    });
    
    $('#frmToken').on('submit',function(e){
        e.preventDefault();
        $('#token_buy').click();
    })
    
    $('#chnPasswordModal').on('hidden.bs.modal', function (e) {
        $(this).find('.success').css('display','none');
        $(this).find('.failed').css('display','none');
    })
    
    $('#editProfileModal').on('hidden.bs.modal', function (e) {
        $(this).find('.success').css('display','none');
        $(this).find('.failed').css('display','none');
    })
    

   
});


    


function filterPageData(urlto,len,extra){
    
    if(typeof len === 'undefined' || len ==''){
        len = $('#len').val();
    }
    if(typeof extra === 'undefined'){
        extra = {};
    }
    
    var crnt = $('#crnt').val();
    var type = $('#type').html();
    
    var id = 'total';
    if(div_id == 'btable'){
         crnt = $('#crntb').val();
         len = $('#lenb :selected').val();
        id = 'totalb';
    }else if(div_id == 'ctable'){
        crnt = $('#crntc').val();
         len = $('#lenc :selected').val();
        id = 'totalc';
    }
    
    var search = $('#searchname').val();

    if(crnt > parseInt($("#"+id).html()) && parseInt($("#"+id).html()) != 0){
       
        return false;
    }
   
    
    if(getData(urlto,crnt,len,type,'',search,extra)==false){
        
        return false;
    }
}

function isNumeric(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}


function filterDataWith(urlto,elem,extra){
   
    var crnt = $('#crnt').val();
    var len = $('#len').val();
    
    if(div_id == 'btable'){
        var crnt = $('#crntb').val();
        var len = $('#lenb :selected').val();
    }else if(div_id == 'ctable'){
        crnt = $('#crntc').val();
         len = $('#lenc :selected').val();
        
    }
    
    var type = $('#type').html();
    var opr = elem.id;
    var search = $('#searchname').val();
    
    if(typeof extra === 'undefined'){
        extra = {};
    }
    
    
    getData(urlto,crnt,len,type,opr,search,extra);
}
function trace(data){
    
    console.log(data);
}
function modalOpen(mdlname,title,msg,onsuccess,id,onabort){
    $('#'+mdlname+'').find('#mdltitle').html(title);
    $('#'+mdlname+'').find('#mdlmsg').html(msg);
    $('#'+mdlname+'').find('#mdlyes').attr('onclick',onsuccess+'('+id+')');
    
    if(onabort!=='')
        $('#'+mdlname+'').find('#mdlabort').attr('onclick',onabort+'()');

    $('#'+mdlname+'').modal('show');

}
