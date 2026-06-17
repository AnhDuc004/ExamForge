<?php

use App\Modules\Attempt\Controllers\AttemptController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('attempts/{id}', [AttemptController::class, 'show']);
    Route::post('attempts/{id}/answers', [AttemptController::class, 'saveAnswer']);
    Route::post('attempts/{id}/submit', [AttemptController::class, 'submit']);
});
