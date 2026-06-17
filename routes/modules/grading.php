<?php

use App\Modules\Grading\Controllers\GradingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'permission:grading,review'])->group(function () {
    Route::get('grading/pending', [GradingController::class, 'pending']);
    Route::put('answers/{id}/review', [GradingController::class, 'reviewAnswer']);
    Route::post('grading/attempts/{id}/finalize', [GradingController::class, 'finalizeAttempt']);
});
