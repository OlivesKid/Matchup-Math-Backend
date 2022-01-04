<!DOCTYPE html>
<html>
    <head>
        <title>Yize Games </title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
        <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=0">
    </head>
    <style type="text/css">
        body, html {
            height: 100%;
        }
        .bg {
            background-image:url(assets/images/index.jpg);
            height: 100%;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }
        .come-text {
            position: absolute;
            top: 46%;
            left: 50%;
            transform: translateX(-50%) translateY(-50%);
        }
        .footer-bottom{
            position:absolute;
            bottom:0px;
            -web-kit-botttom:-20px;
            left:0;
            background-color:#fff;
            width:100%;    
        }
       /* .come-text{
           color:#fff;
           position:absolute;
           top:38%;
           left:37%; 
        }*/
        .come-text h1{
            color:#fff;
            font-size:100px;  
        }
        .logo{
            position: absolute;
            top: 5px;
            left: 10px;
        }
       
        @media (min-width:992px) and (max-width: 1199px){
               /* .come-text {
                    top: 37%;
                    left: 34%;
                }*/
                .come-text h1{
                    font-size:75px;  
                }
        }
        @media (min-width: 768px) and (max-width:991px){
            /*.come-text {
                    top: 37%;
                    left: 33%;
                }*/
                .come-text h1{
                    font-size:60px;  
                }
        }
        @media (min-width:576px) and (max-width: 767px){
                
        }
        @media (max-width: 575px){
            .h4, h4 {
                font-size: 13px;
            }
           /* .come-text {
                    top: 39%;
                    left: 24%;
                }*/
                .come-text h1{
                    font-size:33px;  
                }
        }


        @media(width:414px){
            .come-text h1 {
                font-size: 32px;
            }
        }
        @media(width:412px){
            .come-text h1 {
                font-size: 31px;
            }
        }
         @media (width: 375px){
            /*.come-text {
                    top: 39%;
                    left: 22%;
                }*/
                .come-text h1{
                    font-size:28px ;  
                }
        }
         @media (width: 384px){
            .come-text h1 {
                font-size: 30px;
            }
        }
        @media (width: 360px){
            /*.come-text {
                    top: 39%;
                    left: 21%;
                }*/
                .come-text h1 {
                    font-size: 28px;
                }
        }
        @media (width: 320px){
            .h4, h4 {
                font-size: 11px;
            }
            /*/*.come-text {
                top: 40%;
                left: 18%;
            }*/
            .come-text h1{
                    font-size:24px;  
                }

        }
    </style>
    <body style="overflow-y:hidden;">
            <div class="bg"></div>
            <div class="logo">
                <img src="{{URL::to('assets/images/logo-main.png')}}">
            </div>
            <div class="come-text">
                <h1><b>Coming soon</b></h1>
            </div>
            <div class="footer-bottom" style="padding: 10px">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12 widget"><h4>Copyright © {{date("Y")}} Yize Games  <span class="pull-right"><a href="{{url('privacy-policy')}}">Privacy Policy</a></span></h4>
                        </div>
                    </div>
                </div>
            </div>
    </body>
</html>
