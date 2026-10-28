<?php

use App\Http\Controllers\Api\V1\ProductBulkDeleteController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\TokenController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1
|--------------------------------------------------------------------------
|
| Token issuing is public because it is the entry point for authentication;
| it is rate limited inside App\Http\Requests\Api\IssueTokenRequest. Every
| product endpoint requires an authenticated administrator.
|
*/

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('tokens', [TokenController::class, 'store'])->name('tokens.store');

    Route::middleware('auth:sanctum')->group(function () {
        // Any authenticated user may revoke their own token, admin or not.
        Route::delete('tokens/current', [TokenController::class, 'destroy'])->name('tokens.destroy');

        Route::middleware('admin')->group(function () {
            // Declared before the resource routes so DELETE /products/bulk is
            // not matched as DELETE /products/{product}.
            Route::delete('products/bulk', ProductBulkDeleteController::class)->name('products.bulk-destroy');

            Route::apiResource('products', ProductController::class)
                ->only(['index', 'store', 'show', 'destroy']);
        });
    });
});
