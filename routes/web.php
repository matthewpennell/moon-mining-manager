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

// Login pages.
Route::get('/login', function () {
    return view('login');
})->name('login');
Route::get('/admin', function () {
    return view('admin-login');
})->name('admin-login');

// Public list of upcoming mining timers.
Route::middleware(['login'])->prefix('timers')->group(function () {
    Route::get('/', 'TimerController@home');
    Route::post('/claim/{claim}/{refinery}', 'TimerController@claim');
    Route::get('/clear/{claim}/{refinery}', 'TimerController@clear');
});

// Search interface.
Route::get('/search', 'SearchController@search');

// Admin interface home.
Route::get('/', 'AppController@home')->middleware('admin');

// Access management.
Route::middleware(['admin'])->prefix('access')->group(function () {
    Route::get('/', 'AppController@showAuthorisedUsers');
    Route::get('/new', 'AppController@showUserAccessHistory');
    Route::post('/admin/{id}', 'AppController@makeUserAdmin');
    Route::post('/whitelist/{id}', 'AppController@whitelistUser');
    Route::post('/blacklist/{id}', 'AppController@blacklistUser');
});

// Reports.
Route::middleware(['admin'])->prefix('reports')->group(function () {
    Route::get('/', 'ReportsController@main');
    Route::get('/fix', 'ReportsController@fix');
    Route::get('/regenerate', 'ReportsController@regenerate');
});

// Miner reporting.
Route::middleware(['admin'])->prefix('miners')->group(function () {
    Route::get('/', 'MinerController@showMiners');
    Route::get('/{id}', 'MinerController@showMinerDetails');
});

// Renter management.
Route::middleware(['admin'])->prefix('renters')->group(function () {
    Route::get('/', 'RenterController@showRenters');
    Route::get('/new', 'RenterController@addNewRenter');
    Route::post('/new', 'RenterController@saveNewRenter');
    Route::get('/{id}', 'RenterController@editRenter');
    Route::post('/{id}', 'RenterController@updateRenter');
});

// Payment management.
Route::middleware(['admin'])->prefix('payment')->group(function () {
    Route::get('/new', 'PaymentController@addNewPayment');
    Route::post('/new', 'PaymentController@insertNewPayment');
});

// Tax management.
Route::middleware(['admin'])->prefix('taxes')->group(function () {
    Route::get('/', 'TaxController@showTaxRates');
    Route::get('/history', 'TaxController@showHistory');
    Route::post('/update_value/{id}', 'TaxController@updateValue');
    Route::post('/update_rate/{id}', 'TaxController@updateTaxRate');
    Route::post('/update_master_rate', 'TaxController@updateMasterTaxRate');
    Route::get('/load', 'TaxController@loadInitialTaxRates');
});

// Email template management.
Route::middleware(['admin'])->prefix('emails')->group(function () {
    Route::get('/', 'EmailController@showEmails');
    Route::post('/update', 'EmailController@updateEmails');
});

// Handle EVE SSO requests and callbacks.
Route::get('/sso', 'Auth\AuthController@redirectToProvider');
Route::get('/admin-sso', 'Auth\AuthController@redirectToProviderForAdmin');
Route::get('/callback', 'Auth\AuthController@handleProviderCallback');

// Logout.
Route::get('/logout', 'AppController@logout');
