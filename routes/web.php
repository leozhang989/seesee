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

//deng下载页
Route::get('sssee/download', 'DownloadController@dengDownload');

//new flower下载页
Route::get('flower/download', 'DownloadController@flowerDownload');

//seesee下载页
Route::get('see/download', 'DownloadController@seeDownload');

//newfeng下载页
Route::get('feng/download', 'DownloadController@fengDownload');

//all apps tf无法连接appstore问题解决页
Route::get('app/faq1', function () {
    return view('faq1');
});
