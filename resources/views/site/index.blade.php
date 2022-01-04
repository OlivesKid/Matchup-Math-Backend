@extends('layouts.site')
@section('content')
<section class="slider-area">
   <div class="container">
      <div class="row">
         <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="slider-main">
               <div class="slider-info">
                  <div class="slider-info-logo">
                     <div class="slider-icon">
                        <img src="{{url('assets/site/images/yii-logo.png')}}" alt="">
                     </div>
                     <div class="slider-logo">
                        <img src="{{url('assets/site/images/matchup-logo.png')}}" alt="">
                     </div>
                  </div>
                  <h2>A puzzle game with numbers.</h2>
                  <div class="store-icon">
                     <ul>
                        <li><a href="https://play.google.com/store/apps/details?id=com.yizegames.matchupmath" target="_blank"><img src="{{url('assets/site/images/play-store.png')}}" align=""></a></li>
                        <li><a href="https://apps.apple.com/us/app/matchup-math/id1565128000" target="_blank"><img src="{{url('assets/site/images/app-store.png')}}" align=""></a></li>
                     </ul>
                  </div>
               </div>
               <div class="slider-images">
                  <div class="slide-image-slider">
                     <div class="slide-item">
                        <img src="{{url('assets/site/images/slide.png')}}" align="">
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</section>
@endsection


