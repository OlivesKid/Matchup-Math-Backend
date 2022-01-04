@extends('layouts.site')
@section('header')
<style>
   .header-main {
       position: static;
   }
</style>
@endsection
@section('content')

<section class="play-area page-paddings">
   <div class="container">
      <div class="row">
         <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
            <div class="play-left-box">
               <div class="play-images">
                  <iframe width="450" height="530" src="https://www.youtube.com/embed/nv-jFlHPtRA" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
               </div>
            </div>
         </div>
         <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
            <div class="play-right">
               <p class="theme-description">Object of the game is to use the four randomly generated <b>number cards</b> as denoted by the ‘?’ icon and the <b>math operators</b> to equal the <b>match number</b>, which is also randomly generated, in the fewest steps possible.</p>
               <h3 class="theme-title">Game Play</h3>
               <p class="theme-description">Once a specific game mode is selected, the game play screen will appear. A randomly generated <b>‘match’</b> number will be displayed. After the random match number is shown, the first number card will automatically reveal a randomly generated number. The player also has the option to tap the other number cards to have additional randomly generated numbers displayed. However, the more number cards a player uses, the lower their overall score will be. </p>
               <p class="theme-description">To begin play, the player has to touch the first number card with the revealed number and a math operator of their choice to begin trying to equal the match number. All selections will be displayed on the <b>input board</b> in the sequence of: number – math operator – number – math operator in repeat fashion until the user either equals the match number or runs out of squares on the input board. When the match number is equaled, the player has to tap the equal (=) sign to complete the game.</p>
            </div>
         </div>
      </div>
   </div>
</section>

@endsection
@section('footer')
@endsection