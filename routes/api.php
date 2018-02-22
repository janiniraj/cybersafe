<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['namespace' => 'API', 'as' => 'api.'], function () {
    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@AdminLogin');
    Route::get('fetch-family/{code}', 'AuthController@fetchFamily');
    Route::get('login/{code}', 'AuthController@login');
    Route::post('notification-test', 'AuthController@notificationTest');

    Route::group(['middleware' => ['jwt.auth']], function() {
        Route::post('send-location', 'UserLocationController@add');
        Route::get('fetch-locations/{code}', 'UserLocationController@fetchLocation');
        Route::get('profile', 'UserController@profile');
        Route::get('add-chatroom-id/{id}', 'UserController@addChatRoomId');
        Route::get('fetch-recent-locations', 'UserLocationController@fetchRecentLocationOfFamily');
        Route::get('fetch-recent-location/{code}', 'UserLocationController@fetchRecentLocation');

        Route::get('emergency', 'UserController@emergency');

        Route::get('dismiss-emergency/{code}', 'UserController@dismissEmergency');
    });
});
