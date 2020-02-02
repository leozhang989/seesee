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

Route::group(['middleware' => ['signature']], function() {
    //get users and servers
    Route::get('/user-info', 'AppusersController@getUserInfo');

    //get users and servers test api
//    Route::get('/user-info-test', 'AppusersController@getUserInfoTest');
});
//get users and servers test api
//Route::get('/user-info-test-unsign', 'AppusersController@getUserInfoTestUnsign');
