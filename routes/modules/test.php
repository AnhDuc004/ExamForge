<?php

use App\Modules\Test\Controllers\TestController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('tests', [TestController::class, 'index']);
    Route::get('tests/{id}', [TestController::class, 'show']);

    Route::post('tests', [TestController::class, 'store'])
        ->middleware('permission:tests,build');

    Route::put('tests/{id}', [TestController::class, 'update'])
        ->middleware('permission:tests,build');

    Route::delete('tests/{id}', [TestController::class, 'destroy'])
        ->middleware('permission:tests,build');

    Route::post('tests/{id}/publish', [TestController::class, 'publish'])
        ->middleware('permission:tests,publish');

    Route::post('tests/{testId}/sections', [TestController::class, 'addSection'])
        ->middleware('permission:tests,build');

    Route::put('tests/{testId}/sections/{sectionId}', [TestController::class, 'updateSection'])
        ->middleware('permission:tests,build');

    Route::delete('tests/{testId}/sections/{sectionId}', [TestController::class, 'deleteSection'])
        ->middleware('permission:tests,build');

    Route::post('tests/{testId}/sections/{sectionId}/questions', [TestController::class, 'attachQuestion'])
        ->middleware('permission:tests,build');

    Route::put('tests/{testId}/sections/{sectionId}/questions/{sectionQuestionId}', [TestController::class, 'updateSectionQuestion'])
        ->middleware('permission:tests,build');

    Route::delete('tests/{testId}/sections/{sectionId}/questions/{sectionQuestionId}', [TestController::class, 'deleteSectionQuestion'])
        ->middleware('permission:tests,build');
});
