<!doctype html>
<html lang="en">
   <head>
      <!-- Required meta tags -->
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <!-- CSS -->
      <link rel="stylesheet" href="{{url('assets/site/css/style.css')}}">
      <link rel="stylesheet" href="{{url('assets/site/css/responsive.css')}}">
      <link rel="stylesheet" href="{{url('assets/site/css/bootstrap.min.css')}}">
      <link rel="stylesheet" href="{{url('assets/site/fontawesome/css/all.min.css')}}">
      <link rel="shortcut icon" href="{{url('assets/site/images/favicon.png')}}">
      <!-- CSS -->
      <title>{{$header['title']}}</title>
      @yield('header')
   </head>
   <body>
      <header class="header-main">
         <div class="container">
            <div class="row">
               <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-12">
                  <div class="header-logo">
                     <a href="{{url('/')}}"><img src="{{url('assets/site/images/logo.png')}}" alt="logo"></a>
                  </div>
               </div>
               <div class="col-xl-9 col-lg-9 col-md-9 col-sm-12 col-12">
                  <div class="header-right clearfix">
                     <div class="header-menu">
                        <nav class="navbar navbar-expand-lg navbar-light">
                           <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                              <i class="fas fa-bars"></i>
                           </button>
                           <div class="collapse navbar-collapse" id="navbarSupportedContent">
                              <ul class="navbar-nav mr-auto">
                                 <li class="nav-item menu-menu-parent">
                                    <a class="nav-link " href="{{url('about')}}">About Matchup</a>
                                 </li>
                                 <li class="nav-item menu-menu-parent">
                                    <a class="nav-link" href="{{url('play-game')}}">How To Play</a>
                                 </li>
                              </ul>
                           </div>
                        </nav>
                     </div>   
                     <div class="header-social">
                        <ul class="clearfix">
                           <li><a class="facebook" href="{{config('constant.FACEBOOK_PAGE_URL')}}" target="_blanck"><i class="fab fa-facebook-f"></i></a></li>
                           <li><a class="instagram" href="{{config('constant.INSTAGRAM_PAGE_URL')}}" target="_blanck"><i class="fab fa-instagram" target="_blanck"></i></a></li>
                           <li><a class="twitter" href="{{config('constant.TWITTER_PAGE_URL')}}" target="_blanck"><i class="fab fa-twitter" target="_blanck"></i></a></li>
                        </ul>
                     </div>                  
                  </div>
               </div>
            </div>
         </div>
      </header>