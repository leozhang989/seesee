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

//seesee faq page
Route::get('see/notices/{version}', 'NoticesController@detail');
