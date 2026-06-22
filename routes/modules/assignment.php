<?php

use App\Modules\Assignment\Controllers\AssignmentController;
use App\Modules\Attempt\Controllers\AttemptController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'permission:assignments,manage'])->group(function () {
    // List assignments (paginated)
    Route::get('assignments', [AssignmentController::class, 'index']);

    // Get assignment by ID
    Route::get('assignments/{id}', [AssignmentController::class, 'show']);

    // Create assignment
    Route::post('assignments', [AssignmentController::class, 'store'])
        ->middleware('permission:assignments,manage');

    // Update assignment
    Route::put('assignments/{id}', [AssignmentController::class, 'update'])
        ->middleware('permission:assignments,manage');

    // Delete assignment
    Route::delete('assignments/{id}', [AssignmentController::class, 'destroy'])
        ->middleware('permission:assignments,manage');
});

Route::middleware(['auth:sanctum', 'role:Student,Guest'])->group(function () {
    // List assignments for the authenticated student
    Route::get('assignments/my', [AssignmentController::class, 'my'])->name('assignments.my');

    // Start an attempt for an assigned test
    Route::post('assignments/{assignmentId}/start', [AttemptController::class, 'start'])
        ->name('assignments.start');
});
