<?php

use App\Modules\Role\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::prefix('roles')->middleware(['auth:sanctum', 'permission:tenant,settings'])->group(function () {
    Route::get('/', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/{id}', [RoleController::class, 'show'])->name('roles.show');
    Route::post('/', [RoleController::class, 'store'])->name('roles.store');
    Route::put('/{id}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');
});
