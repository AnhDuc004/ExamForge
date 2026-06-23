<?php

use App\Modules\Grading\Controllers\GradingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('grading/pending', [GradingController::class, 'pending'])
        ->middleware('permission:grading,view-pending');
    Route::put('answers/{id}/review', [GradingController::class, 'reviewAnswer'])
        ->middleware('permission:grading,review-answer');
    Route::post('grading/attempts/{id}/finalize', [GradingController::class, 'finalizeAttempt'])
        ->middleware('permission:grading,finalize');
});
