<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

if (env('APP_ENV') === 'live') {
    \URL::forceSchema('https');
}

// Route::get('/', function () {
//     return view('index');
// });


Route::group(["prefix" => "api","middleware" => ["api"]],function(){
    Route::controller('services','API\ServicesController');
    Route::controller('user','API\UserController');
    Route::controller('services','API\ServicesController');
    Route::controller('game','API\GameController');
});

 Route::controller('user','UserController');
 Route::controller('admin','AdminController');
 
 
 /*------ Cron Routes:Start ------*/

Route::get('cron/declare-challenge-result','GeneralController@declareChallengeResult');
Route::get('cron/robot-play-game','GeneralController@robotChallengePlayerGame');
Route::get('cron/notify-user','GeneralController@Notification');

/*------ Cron Routes:Over ------*/
Route::get('privacy-policy', 'WelcomeController@policy');
Route::get('install-matchup-app', 'WelcomeController@installMatchupApp');

Route::get('/', 'WelcomeController@getIndex');
Route::get('play-game', 'WelcomeController@getPlayGame');
Route::get('about', 'WelcomeController@getAbout');

// Route::get('/', function () {

//     return view('site.index');
// });

