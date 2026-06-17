<?php

use App\Modules\Question\Controllers\QuestionController;
use Illuminate\Support\Facades\Route;

Route::prefix('questions')->middleware('auth:sanctum')->group(function () {
    // List questions with filters
    Route::get('/', [QuestionController::class, 'index'])
        ->middleware('permission:questions,view')
        ->name('questions.index');
    Route::get('/by-status/{status}', [QuestionController::class, 'byStatus'])
        ->middleware('permission:questions,view')
        ->name('questions.by-status');
    Route::get('/by-tags', [QuestionController::class, 'byTags'])
        ->middleware('permission:questions,view')
        ->name('questions.by-tags');

    // Create question
    Route::post('/', [QuestionController::class, 'store'])
        ->middleware('permission:questions,create')
        ->name('questions.store');

    // Bulk import / update
    Route::post('/bulk-import', [QuestionController::class, 'bulkImport'])
        ->middleware('permission:questions,create')
        ->name('questions.bulk-import');
    Route::put('/bulk-update', [QuestionController::class, 'bulkUpdate'])
        ->middleware('permission:questions,update')
        ->name('questions.bulk-update');

    // Get specific question
    Route::get('/{id}', [QuestionController::class, 'show'])
        ->middleware('permission:questions,view')
        ->name('questions.show');

    // Update question
    Route::put('/{id}', [QuestionController::class, 'update'])
        ->middleware('permission:questions,update')
        ->name('questions.update');

    // Delete question
    Route::delete('/{id}', [QuestionController::class, 'destroy'])
        ->middleware('permission:questions,delete')
        ->name('questions.destroy');

    // Publish question
    Route::post('/{id}/publish', [QuestionController::class, 'publish'])
        ->middleware('permission:questions,publish')
        ->name('questions.publish');

    // Archive question
    Route::post('/{id}/archive', [QuestionController::class, 'archive'])
        ->middleware('permission:questions,archive')
        ->name('questions.archive');
});
