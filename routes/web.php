<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

//seesee faq page
Route::get('see/faq', function () {
    return view('faq');
});

//goods list
Route::get('goods/list/{token}', 'PayController@list');

//seesee notice detail page
Route::get('notices/list', 'NoticesController@list');

//seesee notice detail page
Route::get('notices/detail/{id}/{uuid}', 'NoticesController@detail');

//pwd reset page
Route::get('reset-pwd/confirm/{token}/{email}', 'ResetPwdController@resetPage');


//flower set vip page
Route::get('set-flower-vip/{token}', 'AppusersController@setFlowerVip');


//公用使用教程页面
//首页
Route::get('sshelp', function () {
    return view('sshelp.shadowsocks');
});
//windows教程android
Route::get('sshelp/windows', function () {
    return view('sshelp.windows');
});
//android教程
Route::get('sshelp/android', function () {
    return view('sshelp.android');
});
//mac教程
Route::get('sshelp/mac', function () {
    return view('sshelp.mac');
});

//support pay page
Route::get('see/support/pay/{uuid}', 'DownloadController@seeSupportPay');

//deng下载页
Route::get('sssee/download', 'DownloadController@dengDownload');

//new flower下载页
Route::get('flower/download', 'DownloadController@flowerDownload');

//seesee下载页
//Route::get('see/download', 'DownloadController@seeDownload');
Route::get('see/download', function () {
    return view('see-download-notice');
});

//newfeng下载页
//Route::get('feng/download', 'DownloadController@fengDownload');
Route::get('feng/download', function () {
    return view('feng-download-notice');
});

//all apps tf无法连接appstore问题解决页
Route::get('app/faq1', function () {
    return view('faq1');
});
Route::get('app/faq2', function () {
    return view('faq2');
});

//领取进群福利 页面
Route::get('group-gift/{token}', 'ResetPwdController@groupGiftPage');
//Route::get('group-gift/{token}', function () {
//    return view('group-gift');
//});

Route::get('see/common-zhuanyi', function () {
    return view('common-zhuanyi');
});

//seesee有账号VIP转移
Route::get('see/account-zhuanyi/{uuid}', 'AppusersController@seeAccountZhuanyiPage');

//seese设备VIP转移
Route::get('see/device-zhuanyi/{uuid}/{token}', 'AppusersController@seeDeviceZhuanyiPage');


//kang VIP转移
Route::get('kang/transfer-page/uht1i7xlm6wfs8ob', 'KangTransferController@kangTransferPage');

//open vip support pay
Route::get('support/pay/vYmCpFZXwA5BrVES', 'SupportPayController@webpay');


//web transfer per vip
Route::get('kfp/transfer-page/{uuid}', 'KangTransferController@webpermanentTransferPage');

//web transfer ord vip
Route::get('ord/transfer-page/{uuid}', 'KangTransferController@webOrdTransferPage');
