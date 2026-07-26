<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web', 'authh', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu')->prefix('stock-counts')->group(function () {
    Route::get('/', [Modules\StockCount\Http\Controllers\StockCountController::class, 'index'])->name('stock-counts.index');
    Route::get('/create', [Modules\StockCount\Http\Controllers\StockCountController::class, 'create'])->name('stock-counts.create');
    Route::post('/', [Modules\StockCount\Http\Controllers\StockCountController::class, 'store'])->name('stock-counts.store');
    Route::post('/update-status', [Modules\StockCount\Http\Controllers\StockCountController::class, 'updateStatus'])->name('stock-counts.update-status');
    Route::get('/settings', [Modules\StockCount\Http\Controllers\StockCountController::class, 'getSettings'])->name('stock-counts.settings');
    Route::post('/settings', [Modules\StockCount\Http\Controllers\StockCountController::class, 'postSettings'])->name('stock-counts.post-settings');
    Route::get('/compare', [Modules\StockCount\Http\Controllers\StockCountController::class, 'compare'])->name('stock-counts.compare');
    Route::get('/reset-permissions', [Modules\StockCount\Http\Controllers\StockCountController::class, 'resetPermissions'])->name('stock-counts.reset-permissions');
    Route::post('/reset-permissions', [Modules\StockCount\Http\Controllers\StockCountController::class, 'resetPermissions'])->name('stock-counts.post-reset-permissions');
    Route::get('/all-sessions', [Modules\StockCount\Http\Controllers\StockCountController::class, 'getAllSessionsJson'])->name('stock-counts.all-sessions');
    Route::get('/print-pdf-all', [Modules\StockCount\Http\Controllers\StockCountController::class, 'printPdfAll'])->name('stock-counts.print-pdf-all');
    Route::get('/{id}', [Modules\StockCount\Http\Controllers\StockCountController::class, 'show'])->name('stock-counts.show');
    Route::delete('/{id}', [Modules\StockCount\Http\Controllers\StockCountController::class, 'destroy'])->name('stock-counts.destroy');
    Route::get('/{id}/worksheet', [Modules\StockCount\Http\Controllers\StockCountController::class, 'worksheet'])->name('stock-counts.worksheet');
    Route::get('/{id}/print-worksheet', [Modules\StockCount\Http\Controllers\StockCountController::class, 'printWorksheet'])->name('stock-counts.print-worksheet');
    Route::get('/{id}/duplicate', [Modules\StockCount\Http\Controllers\StockCountController::class, 'duplicate'])->name('stock-counts.duplicate');
    Route::post('/{id}/save-progress', [Modules\StockCount\Http\Controllers\StockCountController::class, 'saveWorksheetProgress'])->name('stock-counts.save-progress');
    Route::post('/{id}/update-name', [Modules\StockCount\Http\Controllers\StockCountController::class, 'updateName'])->name('stock-counts.update-name');
    Route::post('/{id}/scan-barcode', [Modules\StockCount\Http\Controllers\StockCountController::class, 'scanBarcode'])->name('stock-counts.scan-barcode');
    Route::post('/{id}/reconcile', [Modules\StockCount\Http\Controllers\StockCountController::class, 'reconcile'])->name('stock-counts.reconcile');
    Route::get('/{id}/export', [Modules\StockCount\Http\Controllers\StockCountController::class, 'export'])->name('stock-counts.export');
    Route::post('/{id}/import-excel', [Modules\StockCount\Http\Controllers\StockCountController::class, 'importExcel'])->name('stock-counts.import-excel');
    Route::get('/{id}/download-template', [Modules\StockCount\Http\Controllers\StockCountController::class, 'downloadTemplate'])->name('stock-counts.download-template');
    Route::get('/{id}/filter-data', [Modules\StockCount\Http\Controllers\StockCountController::class, 'getFilteredData'])->name('stock-counts.filter-data');
});
