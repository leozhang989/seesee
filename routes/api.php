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

    //register when pay
    Route::post('/register', 'RegisterController@register');

    //use login api
    Route::post('/login', 'AppusersController@login');

    //logout
    Route::post('/logout', 'AppusersController@logout');

    //get pwd reset url
    Route::get('/reset', 'ResetPwdController@resetPassword');

    //get user vip time
    Route::post('/query-user-vip', 'AppusersController@queryUserVip');

    //get recharge list
    Route::get('/get/recharges', 'SupportPayController@rechargeList');

    //recharge api
    Route::post('/recharge', 'SupportPayController@recharge');

    //set settlement
    Route::post('/settlement', 'SupportPayController@settlement');

    //add vip
    Route::post('/add-vip', 'AppusersController@addVip');

    //pc account servers
    Route::get('/account-servers', 'AppusersController@accountServerList');

    //get servers
    Route::get('/app-servers', 'AppusersController@serverList');

    //get random servers
    Route::get('/random-servers', 'AppusersController@newServerList');

    //get random servers by all apps
    Route::get('/servers-list', 'AppusersController@appServerList');
});


//reset pwd api
Route::post('/reset-pwd', 'ResetPwdController@newPassword');

Route::group(['middleware' => ['throttle:20']], function() {
    //进群领福利
    Route::post('/get-group-gift', 'ResetPwdController@getGroupGift');
});

//发起支付
Route::get('/order/create', 'PayController@createOrder');

//查询到期时间接口
Route::get('/user/vip-time', 'PayController@getVipexpireat');

//paddle回调webhook接口
Route::post('/paddle/web-hook', 'PayController@webHook');

//server list
Route::get('/servers', 'AppusersController@servers');
