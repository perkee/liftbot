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

call_user_func(function () {

    $apiBase = 'api/v1';

    Route::get('', function () {
        return 'web interface coming soon!';
    });

    Route::group(['middleware' => 'slack'], function () use ($apiBase) {
        Route::post("$apiBase/slack", 'Slack@handle');
    });

});
