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

call_user_func(function(){

	$apiBase = 'api/v1';

	Route::get('/', function () {
	    //abort(401);
		return "yuo found liftbot" . PHP_EOL;
	});

	Route::post("$apiBase/slack",function(){
		return 'hello liftbot is here' .PHP_EOL;
	});
});
