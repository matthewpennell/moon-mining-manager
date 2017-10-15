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

// Access management.
Route::get('/access', 'AppController@showAuthorisedUsers');
Route::get('/access/new', 'AppController@showUserAccessHistory');
Route::post('/access/whitelist/{id}', 'AppController@whitelistUser');
Route::post('/access/blacklist/{id}', 'AppController@blacklistUser');

// Tax management.
Route::get('/taxes', 'TaxController@showTaxRates');
Route::post('/taxes/update_value/{id}', 'TaxController@updateValue');
Route::post('/taxes/update_rate/{id}', 'TaxController@updateTaxRate');
Route::post('/taxes/update_master_rate', 'TaxController@updateMasterTaxRate');

// Handle EVE SSO requests and callbacks.
Route::get('/login', 'Auth\AuthController@redirectToProvider');
Route::get('/callback', 'Auth\AuthController@handleProviderCallback');

// Logout.
Route::get('/logout', 'AppController@logout');

// Cron routes.
Route::get('/cron/refineries', 'CronController@pollRefineries');
Route::get('/cron/observers', 'CronController@pollMiningObservers');
Route::get('/cron/wallet', 'CronController@pollWallet');
Route::get('/cron/invoices', 'CronController@generateInvoices');
