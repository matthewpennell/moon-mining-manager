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

// Master route.
Route::get('/', 'AppController@home');

// Handle EVE SSO requests and callbacks.
Route::get('/login', 'Auth\AuthController@redirectToProvider');
Route::get('/callback', 'Auth\AuthController@handleProviderCallback');

// Cron job.
Route::get('/cron/refresh', 'CronController@refresh');

// Logout.
Route::get('/logout', 'AppController@logout');
