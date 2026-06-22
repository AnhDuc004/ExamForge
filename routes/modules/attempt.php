<?php

use App\Modules\Attempt\Controllers\AttemptController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:Student,Guest'])->group(function () {
    Route::get('attempts/{id}', [AttemptController::class, 'show']);
    Route::post('attempts/{id}/heartbeat', [AttemptController::class, 'heartbeat']);
    Route::post('attempts/{id}/resume', [AttemptController::class, 'resume']);
    Route::patch('attempts/{id}/answers', [AttemptController::class, 'saveAnswer']);
    Route::post('attempts/{id}/answers', [AttemptController::class, 'saveAnswer']);
    Route::post('attempts/{id}/submit', [AttemptController::class, 'submit']);
});

Route::middleware(['auth:sanctum', 'permission:attempts,force-submit'])->group(function () {
    Route::post('attempts/{id}/force-submit', [AttemptController::class, 'forceSubmit']);
});
