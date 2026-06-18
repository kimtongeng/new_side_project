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

use Modules\Loan\Http\Controllers\LoanController;
use Modules\Loan\Http\Controllers\LoanManagementController;
use Modules\Loan\Http\Controllers\PaymentsController;
use Modules\Loan\Http\Controllers\ReportsController;
use Modules\Loan\Http\Controllers\SettingsController;
use Modules\Loan\Http\Controllers\VertexAIController;
use Modules\Loan\Http\Controllers\LoanPosController;
use Modules\Loan\Http\Controllers\LoanPosEditController;
use Modules\Loan\Http\Controllers\LoanPosUtilityController;
use Modules\Loan\Http\Controllers\LoanPosOfflineController;

// General route for the Loan module
Route::prefix('Loan')->group(function () {
    Route::get('/', [LoanController::class, 'index']);
});

// Routes with middleware
Route::middleware(['web', 'SetSessionData', 'auth', 'language', 'timezone', 'AdminSidebarMenu'])
    ->prefix('Loan')
    ->group(function () {
        // Dashboard
        Route::get('dashboard', [LoanController::class, 'dashboard'])->name('Loan.dashboard');

        // Clients routes
        Route::get('clients', [LoanController::class, 'clients'])->name('Loan.clients');

        // Loans routes
        Route::get('loans', [LoanManagementController::class, 'index'])->name('Loan.loans.index');
        Route::get('loans/create', [LoanManagementController::class, 'create'])->name('Loan.loans.create');
        Route::post('loans', [LoanManagementController::class, 'store'])->name('Loan.loans.store');
        Route::get('loans/{id}/edit', [LoanManagementController::class, 'edit'])->name('Loan.loans.edit');
        Route::put('loans/{id}', [LoanManagementController::class, 'update'])->name('Loan.loans.update');
        Route::get('loans/{id}', [LoanManagementController::class, 'show'])->name('Loan.loans.show');
        Route::delete('loans/{id}', [LoanManagementController::class, 'destroy'])->name('Loan.loans.destroy');

        // Payments routes
        Route::get('payments', [PaymentsController::class, 'index'])->name('Loan.payments.index');
        Route::get('payments/{id}', [PaymentsController::class, 'show'])->name('Loan.payments.show');
        Route::get('payments/export/excel', [PaymentsController::class, 'exportExcel'])->name('Loan.payments.export.excel');
        Route::post('loans/{loan}/payments', [LoanManagementController::class, 'storePayment'])->name('Loan.payments.store');

        // Reports routes
        Route::get('reports', [ReportsController::class, 'index'])->name('Loan.reports.index');
        Route::get('reports/export', [ReportsController::class, 'exportLoans'])->name('Loan.reports.export');

        // Settings routes
        Route::get('settings', [SettingsController::class, 'index'])->name('Loan.settings');
        Route::put('settings', [SettingsController::class, 'update'])->name('Loan.settings.update');

        // Vertex AI routes
        Route::get('vertex-ai', [VertexAIController::class, 'index'])->name('Loan.vertex-ai.index');
        Route::post('vertex-ai/generate', [VertexAIController::class, 'generate'])->name('Loan.vertex-ai.generate');

        // Point of Sale routes
        Route::get('pos', [LoanPosController::class, 'index'])->name('Loan.pos.index');
        Route::get('pos/create', [LoanPosController::class, 'create'])->name('Loan.pos.create');
        Route::post('pos', [LoanPosController::class, 'store'])->name('Loan.pos.store');
        Route::get('pos/{id}/edit', [LoanPosEditController::class, 'edit'])->name('Loan.pos.edit');
        Route::put('pos/{id}', [LoanPosEditController::class, 'update'])->name('Loan.pos.update');

        // POS Utility routes
        Route::get('pos/product-row/{variation_id}/{location_id}', [LoanPosUtilityController::class, 'getProductRow'])->name('Loan.pos.get_product_row');
        Route::get('pos/payment-row', [LoanPosUtilityController::class, 'getPaymentRow'])->name('Loan.pos.get_payment_row');
        Route::get('pos/recent-transactions', [LoanPosUtilityController::class, 'getRecentTransactions'])->name('Loan.pos.get_recent_transactions');
        Route::get('pos/product-suggestion', [LoanPosUtilityController::class, 'getProductSuggestion'])->name('Loan.pos.get_product_suggestion');

        // Offline POS routes
        Route::post('pos/sync-offline', [LoanPosOfflineController::class, 'syncOfflineTransactions'])->name('Loan.pos.sync_offline');
        Route::get('pos/check-connectivity', [LoanPosOfflineController::class, 'checkConnectivity'])->name('Loan.pos.check_connectivity');

        // Installation routes
        Route::get('install', [Modules\Loan\Http\Controllers\InstallController::class, 'index'])->name('Loan.install');
        Route::get('install/update', [Modules\Loan\Http\Controllers\InstallController::class, 'update'])->name('Loan.install.update');
        Route::get('install/uninstall', [Modules\Loan\Http\Controllers\InstallController::class, 'uninstall'])->name('Loan.install.uninstall');
    });