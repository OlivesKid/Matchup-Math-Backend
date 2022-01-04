<html lang="en"><!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="csrf-token" content="{{ csrf_token()}}">
        <title><?php echo isset($header['title']) ? $header['title'] : '' ?></title>  
        <link rel="icon" type="image/png" href="{{url('favicon.png')}}">

        <link rel="stylesheet" href="{{url('assets/css/app.min.css')}}" type="text/css">
        <!--<link rel="stylesheet" href="{{url('assets/css/light-green.min.css')}}" type="text/css">-->
        <script src="{{url('assets/js/app.min.js')}}" ></script>

        <link href="https://fonts.googleapis.com/css?family=Lato:300,400, 700" rel="stylesheet">

        <?php
//        dd($header);
        if (isset($header['css']) && count($header['css']) > 0)
            for ($i = 0; $i < count($header['css']); $i++)
                if (strpos($header['css'][$i], "http://") !== FALSE)
                    echo '<link rel="stylesheet" type="text/css" href="' . $header['css'][$i] . '"/>';
                else
                    echo '<link rel="stylesheet" type="text/css" href="' . url("/") . "/" . $header['css'][$i] . '"/>';




        if (isset($header['js']) && count($header['js']) > 0)
            for ($i = 0; $i < count($header['js']); $i++) {
                if (strpos($header['js'][$i], "http://") !== FALSE)
                    echo '<script type="text/javascript" src="' . $header['js'][$i] . '"></script>';
                else
                    echo '<script type="text/javascript" src="' . url("/") . "/" . $header['js'][$i] . '"></script>';
            }
        ?>

        <?php if (env("APP_ENV") == "live") { ?>
            <script>

            </script>
        <?php } ?>
            @yield('header')
            <style>
            .hm{
                margin-left:18px;
                margin-right:18px;
                margin-top: 21px;
                font-size: 14px;
                font-weight:700;
                color: #585858;
            }
            .pipe{
                margin-top:21px;
                font-weight: bold;
                font-size: 14px;
            }
        </style>

    </head>
    <body>
           <input type="hidden" id="token" value="{{csrf_token()}}">
           <input type="hidden" id="base_url" value="{{URL::to('/')}}/">
        <div class="app">
            @include('admin.admin_sidebar')
            
            <div id="content" class="app-content" role="main">
                <div class="box">
                    
                    <div class="navbar md-whiteframe-z1 no-radius bg-white">
                        <a class="navbar-item pull-left visible-xs visible-sm  m-v" id="toggle_btn" data-toggle="modal" data-target="#aside" tabindex="0" >
                            <i class="mdi-navigation-menu i-24"></i>
                        </a>
                        <div class="navbar-item pull-left h4"><?php echo $body['lable']; ?></div>
         
                        <ul class="nav navbar-tool pull-right v-m">
                            <li class="dropdown m-v hm" >
                                <span ></span>
                            </li>
                            
                            <!--<li class="dropdown m-v pipe">|</li>-->
                            
                            <li class="dropdown m-v hm" >
                                <span></span>
                            </li>
                            
<!--                            <li class="dropdown m-v pipe">|</li>-->
                            
                            <li class="dropdown m-v hm" >
                                <span > </span>
                            </li>
                            
                            <!--<li class="dropdown m-v pipe" >|</li>-->
                            
                            <li class="dropdown m-v hm">
                                <span >{{\General::commonData('name')}}</span>
                            </li>
                            
                            <li class="dropdown m-v pipe" >|</li>
                            
                            <li class="dropdown m-v hm">
                                     <a md-ink-ripple="" href="{{URL::to('admin/logout')}}" style="margin-top:2px;">
                                    <i class="fa fa-sign-out" style="color:#202020;">Sign out</i>
                                </a>
                            </li>
                            
                        </ul>
                    </div>
                    <div class="box-row">
                        <div class="box-cell">
                            
