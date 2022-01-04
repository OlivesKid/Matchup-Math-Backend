<html>
<title>Email Verification</title>

<link rel="stylesheet" href="{{URL::to('assets/css/app.min.css')}}">
<link href="https://fonts.googleapis.com/css?family=Roboto:100,100i,300,300i,400,400i,500,500i,700,700i,900,900i" rel="stylesheet">

<script type="text/javascript" src="{{URL::to('assets/js/app.min.js')}}"></script>

<style>
    .email-bg{
        
    }
    .body-bg{
        background-color: white;
    }
    .success{
        color:#42c666;
        font-family:'Roboto',medium;
      
    }
    .success-msg{        
        color: #63788f;
        font-family: 'Roboto', Regular;
        margin-top: 20px;
    }
    .responsive-logo {
       width: 100%;
        height: auto;
}
</style>
</head>
<body class="body-bg">
    <div class="errore aaaa ">
         <div style="float: left;"><img src="{{URL::to('assets/images/logo_matchup.png')}}" alt="{{config('constant.PLATFORM_NAME')}}" class="responsive-logo"></div>
        <div class="container text-center">
            <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="content-404" style="padding:10% 0;">
                        <div class="four" >
                            <img src="{{URL::to('assets/images/true_sign.png')}}" >
                            <h1 style="" >
                    <span class="success">Successfully Verified!</span></h1>
                            <h4 style="" class="success-msg">Congratulations! You have   successfully verified the email address.</h4>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>


