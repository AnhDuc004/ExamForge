<?php

use App\Modules\User\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('users')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [UserController::class, 'index'])
        ->middleware('permission:users,view')
        ->name('users.index');
    Route::get('/{id}', [UserController::class, 'show'])
        ->middleware('permission:users,view')
        ->name('users.show');
    Route::post('/', [UserController::class, 'store'])
        ->middleware('permission:users,create')
        ->name('users.store');
    Route::put('/{id}/status', [UserController::class, 'updateStatus'])
        ->middleware('permission:users,update-status')
        ->name('users.status');
    Route::put('/{id}', [UserController::class, 'update'])
        ->middleware('permission:tenant,manage')
        ->name('users.update');
    Route::delete('/{id}', [UserController::class, 'destroy'])
        ->middleware('permission:tenant,manage')
        ->name('users.destroy');
});
