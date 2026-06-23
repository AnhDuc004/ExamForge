<?php

use App\Modules\Attempt\Controllers\AttemptController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:Student,Guest'])->group(function () {
    Route::get('attempts/{id}', [AttemptController::class, 'show'])
        ->middleware('permission:attempts,view');
    Route::post('attempts/{id}/heartbeat', [AttemptController::class, 'heartbeat'])
        ->middleware('permission:attempts,heartbeat');
    Route::post('attempts/{id}/resume', [AttemptController::class, 'resume'])
        ->middleware('permission:attempts,resume');
    Route::patch('attempts/{id}/answers', [AttemptController::class, 'saveAnswer'])
        ->middleware('permission:attempts,save-answers');
    Route::post('attempts/{id}/answers', [AttemptController::class, 'saveAnswer'])
        ->middleware('permission:attempts,save-answers');
    Route::post('attempts/{id}/submit', [AttemptController::class, 'submit'])
        ->middleware('permission:attempts,submit');
});

Route::middleware(['auth:sanctum', 'permission:attempts,force-submit'])->group(function () {
    Route::post('attempts/{id}/force-submit', [AttemptController::class, 'forceSubmit']);
});
