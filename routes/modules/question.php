<?php

use App\Modules\Question\Controllers\QuestionController;
use Illuminate\Support\Facades\Route;

Route::prefix('questions')->middleware('auth:sanctum')->group(function () {
    // List all questions or filter by status/tags
    Route::get('/', [QuestionController::class, 'index'])->name('questions.index');
    Route::get('/by-status/{status}', [QuestionController::class, 'byStatus'])->name('questions.by-status');
    Route::get('/by-tags', [QuestionController::class, 'byTags'])->name('questions.by-tags');

    // Create question (requires permission:question,create)
    Route::post('/', [QuestionController::class, 'store'])
        ->middleware('permission:question,create')
        ->name('questions.store');

    // Get specific question
    Route::get('/{id}', [QuestionController::class, 'show'])->name('questions.show');

    // Update question (requires permission:question,update)
    Route::put('/{id}', [QuestionController::class, 'update'])
        ->middleware('permission:question,update')
        ->name('questions.update');

    // Delete question (requires permission:question,delete)
    Route::delete('/{id}', [QuestionController::class, 'destroy'])
        ->middleware('permission:question,delete')
        ->name('questions.destroy');

    // Publish question (requires permission:question,publish)
    Route::post('/{id}/publish', [QuestionController::class, 'publish'])
        ->middleware('permission:question,publish')
        ->name('questions.publish');

    // Archive question (requires permission:question,archive)
    Route::post('/{id}/archive', [QuestionController::class, 'archive'])
        ->middleware('permission:question,archive')
        ->name('questions.archive');
});
