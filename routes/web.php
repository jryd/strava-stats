<?php

use Illuminate\Support\Facades\Route;

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

Route::get('login', ['as' => 'login', 'uses' => 'LoginController@redirectToProvider']);
Route::get('login/callback', ['as' => 'login.callback', 'uses' => 'LoginController@handleCallback']);

Route::get('webhooks/strava', [
    'as' => 'webhooks.strava',
    'uses' => 'StravaWebhookController',
    'middleware' => 'confirm.strava.subscription',
]);
