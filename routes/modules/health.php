<?php

use App\Modules\Health\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'permission:system,health'])->group(function () {
    Route::get('health', [HealthController::class, 'index']);
});
