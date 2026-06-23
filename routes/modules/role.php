<?php

use App\Modules\Role\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::prefix('roles')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [RoleController::class, 'index'])
        ->middleware('permission:roles,view')
        ->name('roles.index');
    Route::get('/{id}', [RoleController::class, 'show'])
        ->middleware('permission:roles,view')
        ->name('roles.show');
    Route::post('/', [RoleController::class, 'store'])
        ->middleware('permission:tenant,settings')
        ->name('roles.store');
    Route::put('/{id}', [RoleController::class, 'update'])
        ->middleware('permission:tenant,settings')
        ->name('roles.update');
    Route::delete('/{id}', [RoleController::class, 'destroy'])
        ->middleware('permission:tenant,settings')
        ->name('roles.destroy');
});
