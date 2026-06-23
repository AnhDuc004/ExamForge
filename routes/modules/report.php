<?php

use App\Modules\Report\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('reports/tests/{id}', [ReportController::class, 'testReport'])
        ->middleware('permission:reports,view');
    Route::post('reports/tests/{id}/export', [ReportController::class, 'exportTestReport'])
        ->middleware('permission:reports,export');
    Route::get('jobs/{jobId}/download', [ReportController::class, 'downloadJob'])
        ->middleware('permission:jobs,download');
});
