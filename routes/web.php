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

Route::get('/auth/{provider}/redirect', [
    'as' => 'social_redirect',
    'uses' => 'SocialController@redirect'
]);

Route::get('/auth/{provider}/callback', [
    'as' => 'social_handle',
    'uses' => 'SocialController@callback'
]);

Route::get('/auth/sign_out', [
    'as' => 'sign_out',
    'uses' => 'SocialController@sign_out'
]);