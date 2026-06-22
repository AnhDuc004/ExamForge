<?php

use App\Modules\Ai\Controllers\AiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('ai/generate-questions', [AiController::class, 'generateQuestions'])
        ->middleware('permission:ai,generate-questions');
    Route::post('ai/suggest-feedback', [AiController::class, 'suggestFeedback'])
        ->middleware('permission:ai,suggest-feedback');
});
