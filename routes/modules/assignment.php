<?php

use App\Modules\Assignment\Controllers\AssignmentController;
use App\Modules\Attempt\Controllers\AttemptController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    // List assignments (paginated)
    Route::get('assignments', [AssignmentController::class, 'index'])
        ->middleware('permission:assignments,create');

    // List selectable students for assignment
    Route::get('assignments/selectable-students', [AssignmentController::class, 'selectableStudents'])
        ->middleware('permission:assignments,create');

    // Create assignment
    Route::post('assignments', [AssignmentController::class, 'store'])
        ->middleware('permission:assignments,create');

    // Get assignment by ID
    Route::get('assignments/{id}', [AssignmentController::class, 'show'])
        ->middleware('permission:assignments,create')
        ->whereUuid('id');

    // Update assignment
    Route::put('assignments/{id}', [AssignmentController::class, 'update'])
        ->whereUuid('id')
        ->middleware('permission:assignments,create');

    // Delete assignment
    Route::delete('assignments/{id}', [AssignmentController::class, 'destroy'])
        ->whereUuid('id')
        ->middleware('permission:assignments,create');
});

Route::middleware(['auth:sanctum', 'permission:assignments,view'])->group(function () {
    // List assignments for the authenticated student
    Route::get('assignments/my', [AssignmentController::class, 'my'])->name('assignments.my');
});

Route::middleware(['auth:sanctum', 'role:Student,Guest'])->group(function () {
    // Start an attempt for an assigned test
    Route::post('assignments/{assignmentId}/start', [AttemptController::class, 'start'])
        ->middleware('permission:attempts,start')
        ->name('assignments.start');
});
