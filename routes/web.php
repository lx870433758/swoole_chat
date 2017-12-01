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


Route::group(['middleware' => 'auth.user'], function () {
    Route::get('/client/index','ClientController@index');
    Route::get('/','ClientController@index');
    Route::post('/client/user_bind','ClientController@user_bind');
    Route::post('/client/get_user_info','ClientController@get_user_info');
});
Route::group(['prefix' => 'auth', 'namespace' => 'Auth'], function () {
    Route::get('login', 'LoginController@getLogin');
    Route::post('post_login', 'LoginController@postLogin');
    Route::get('logout', 'LoginController@logout');
    Route::post('post_register', 'RegisterController@postRegister');
    Route::post('check_date', 'RegisterController@checkDate');
});