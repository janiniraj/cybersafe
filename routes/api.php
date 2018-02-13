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

    Route::group(['middleware' => ['jwt.auth']], function() {
        Route::post('send-location', 'UserLocationController@add');
        Route::get('fetch-locations/{id}', 'UserLocationController@fetchLocation');
    });
});
