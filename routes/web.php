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
