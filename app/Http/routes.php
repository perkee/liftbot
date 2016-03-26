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

Route::get('/', function () {
    //abort(401);
	return "yuo found liftbot" . PHP_EOL;
});

Route::post('/',function(){
	//return 'hello liftbot is here' .PHP_EOL;
});

Route::any('lift/{movement}/{weight}/{units}',function($mvmt,$weight,$units){
	return "your ${mvmt}ed $weight $units" . PHP_EOL;
});

Route::any('lift/{whatever}',function($mvmt,$whatever){
	return "lifted $whatever" . PHP_EOL;
});
