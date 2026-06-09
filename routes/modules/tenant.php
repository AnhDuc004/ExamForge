<?php

use App\Modules\Tenant\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

Route::prefix('tenants')->group(function () {
    Route::middleware(['auth:sanctum', 'permission:tenant,manage'])->group(function () {
        Route::get('/', [TenantController::class, 'index'])->name('tenants.index');
        Route::post('/', [TenantController::class, 'store'])->name('tenants.store');
        Route::get('{tenant}', [TenantController::class, 'show'])->name('tenants.show');
        Route::put('{tenant}', [TenantController::class, 'update'])->name('tenants.update');
        Route::delete('{tenant}', [TenantController::class, 'destroy'])->name('tenants.destroy');
    });
});
