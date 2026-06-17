<?php

use App\Modules\Report\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'permission:grading,review'])->group(function () {
    Route::get('reports/tests/{id}', [ReportController::class, 'testReport']);
    Route::post('reports/tests/{id}/export', [ReportController::class, 'exportTestReport']);
    Route::get('jobs/{jobId}/download', [ReportController::class, 'downloadJob']);
});
