<?php

use App\Modules\Audit\Controllers\AuditController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'permission:audit,view'])->group(function () {
    Route::get('audit-logs', [AuditController::class, 'index']);
});
