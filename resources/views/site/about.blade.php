@extends('layouts.site')
@section('header')
<style>
   .header-main {
       position: static;
   }
</style>
@endsection
@section('content')
<section class="about-area page-paddings">
   <div class="container">
      <div class="row">
         <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
            <div class="about-left text-center">
               <h2>The fun. The challenge. the addiction.</h2>
               <div class="about-images">
                  <img src="{{url('assets/site/images/about.png')}}" alt="">
               </div>
               <h3>Download Now</h3>
               <div class="store-icon">
                  <ul>
                     <li><a href="https://play.google.com/store/apps/details?id=com.yizegames.matchupmath" target="_blank"><img src="{{url('assets/site/images/play-store.png')}}" align=""></a></li>
                     <li><a href="https://apps.apple.com/us/app/matchup-math/id1565128000" target="_blank"><img src="{{url('assets/site/images/app-store.png')}}" align=""></a></li>
                  </ul>
               </div>
            </div>
         </div>
         <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
            <div class="about-right">
               <p class="theme-description">MatchUp Math is a fun and engaging game of mathematical strategy that is a puzzle game with numbers. Play by yourself or against family and friends by selecting the multiplayer option. With thirty levels built into the game, MatchUp is designed to develop critical thinking skills, strengthen problem solving and boost math aptitude.</p>
               <div class="about-list">
                  <ul>
                     <li>Choose Classic to compete on the basis of score.</li>
                     <li>Challenge yourself with TimeMaster to play against the clock.</li>
                     <li>Select multiplayer option to contend against family and friends regardless of their location.</li>
                     <li>Build-in intelligence tracks problem solving ability in order to increase or decrease difficulty of the game.</li>
                     <li>Personalized stat screen to track average score, highest score and number of games played.</li>
                  </ul>
               </div>
               <div class="follow-us-box">
                  <h3>Follow us on social media.</h3>
                  <div class="follow-social">
                     <ul class="clearfix">
                        <li><a class="facebook" href="{{config('constant.FACEBOOK_PAGE_URL')}}" target="_blank"><i class="fab fa-facebook-f"></i></a></li>
                        <li><a class="instagram" href="{{config('constant.INSTAGRAM_PAGE_URL')}}" target="_blank"><i class="fab fa-instagram"></i></a></li>
                        <li><a class="twitter" href="{{config('constant.TWITTER_PAGE_URL')}}" target="_blank"><i class="fab fa-twitter"></i></a></li>
                     </ul>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</section>
@endsection