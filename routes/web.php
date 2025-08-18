<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Sales routes
Route::prefix('sales')->name('sales.')->group(function () {
    Route::get('/', [SaleController::class, 'index'])->name('index');
    Route::post('/import', [SaleController::class, 'import'])->name('import');
    Route::post('/export', [SaleController::class, 'export'])->name('export');
    Route::get('/export/status/{jobId}', [SaleController::class, 'exportStatus'])->name('export.status');
    Route::get('/export/download/{jobId}', [SaleController::class, 'download'])->name('export.download');
});

// Report routes
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/top-products', [ReportController::class, 'topProducts'])->name('top-products');
    Route::get('/monthly-sales', [ReportController::class, 'monthlySales'])->name('monthly-sales');
    Route::get('/low-stock', [ReportController::class, 'lowStock'])->name('low-stock');
    Route::get('/sales-trend', [ReportController::class, 'salesTrend'])->name('sales-trend');
    
    // Export routes
    Route::get('/top-products/export', [ReportController::class, 'exportTopProducts'])->name('top-products.export');
    Route::get('/monthly-sales/export', [ReportController::class, 'exportMonthlySales'])->name('monthly-sales.export');
    Route::get('/low-stock/export', [ReportController::class, 'exportLowStock'])->name('low-stock.export');
    Route::get('/sales-trend/export', [ReportController::class, 'exportSalesTrend'])->name('sales-trend.export');
});
