<?php

use App\Modules\Assignment\Controllers\AssignmentController;
use Illuminate\Support\Facades\Route;

Route::middleware('tenant')->group(function () {
    Route::get('assignments/verify', [AssignmentController::class, 'verify'])->name('assignments.verify');
    Route::post('assignments/verify-token', [AssignmentController::class, 'verifyAccessToken'])->name('assignments.verify-token');
});
