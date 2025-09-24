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

// Route::prefix('exchangecurrency')->group(function() {
//     Route::get('/', 'ExchangeCurrencyController@index');
// });

use Illuminate\Support\Facades\Route;
use Modules\ExchangeCurrency\Http\Controllers\ExchangeCurrencyController;

Route::middleware(['setData', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu', 'CheckUserLogin'])->group(function () {
  Route::resource('exchange_currency', 'Modules\ExchangeCurrency\Http\Controllers\ExchangeCurrencyController');
//   Route::resource('exchange_currency', ExchangeCurrencyController::class);
  Route::post("exchange_currency/update-status/{id}", [ExchangeCurrencyController::class, 'update_status']);
  Route::get('/install', [Modules\ExchangeCurrency\Http\Controllers\InstallController::class, 'index']);
  Route::post('/install', [Modules\ExchangeCurrency\Http\Controllers\InstallController::class, 'install']);
  Route::get('/install/uninstall', [Modules\ExchangeCurrency\Http\Controllers\InstallController::class, 'uninstall']);
  Route::get('/install/update', [Modules\ExchangeCurrency\Http\Controllers\InstallController::class, 'update']);
});
