<?php

use App\Modules\Permission\Controllers\PermissionController;
use Illuminate\Support\Facades\Route;

Route::prefix('permissions')->middleware(['auth:sanctum', 'permission:tenant,settings'])->group(function () {
    Route::get('/', [PermissionController::class, 'index'])->name('permissions.index');
    Route::get('/{id}', [PermissionController::class, 'show'])->name('permissions.show');
    Route::post('/', [PermissionController::class, 'store'])->name('permissions.store');
    Route::put('/{id}', [PermissionController::class, 'update'])->name('permissions.update');
    Route::delete('/{id}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
});
