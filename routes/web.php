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
Route::prefix('access')->group(function () {
    Route::get('/', 'AppController@showAuthorisedUsers');
    Route::get('/new', 'AppController@showUserAccessHistory');
    Route::post('/whitelist/{id}', 'AppController@whitelistUser');
    Route::post('/blacklist/{id}', 'AppController@blacklistUser');
});

// Miner reporting.
Route::prefix('miners')->group(function () {
    Route::get('/', 'MinerController@showMiners');
    Route::get('/{id}', 'MinerController@showMinerDetails');
});

// Payment management.
Route::prefix('payment')->group(function () {
    Route::get('/new', 'PaymentController@addNewPayment');
    Route::post('/new', 'PaymentController@insertNewPayment');
});

// Tax management.
Route::prefix('taxes')->group(function () {
    Route::get('/', 'TaxController@showTaxRates');
    Route::post('/update_value/{id}', 'TaxController@updateValue');
    Route::post('/update_rate/{id}', 'TaxController@updateTaxRate');
    Route::post('/update_master_rate', 'TaxController@updateMasterTaxRate');
    Route::get('/load', 'TaxController@loadInitialTaxRates');
});

// Email template management.
Route::prefix('emails')->group(function () {
    Route::get('/', 'EmailController@showEmails');
    Route::post('/update', 'EmailController@updateEmails');
});

// Handle EVE SSO requests and callbacks.
Route::get('/login', 'Auth\AuthController@redirectToProvider');
Route::get('/callback', 'Auth\AuthController@handleProviderCallback');

// Logout.
Route::get('/logout', 'AppController@logout');

// Cron routes.
Route::prefix('cron')->group(function () {
    Route::get('/refineries', 'CronController@pollRefineries');
    Route::get('/observers', 'CronController@pollMiningObservers');
    Route::get('/wallet', 'CronController@pollWallet');
    Route::get('/invoices', 'CronController@generateInvoices');
});
