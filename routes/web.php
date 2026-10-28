<?php

use App\Http\Controllers\Admin\ProductBulkDeleteController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductExportController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CatalogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public storefront
|--------------------------------------------------------------------------
*/

Route::get('/', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/products/{product}', [CatalogController::class, 'show'])->name('catalog.show');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
});

Route::post('logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Admin dashboard
|--------------------------------------------------------------------------
|
| Every route here requires an authenticated administrator. The export and
| bulk-delete routes are declared before the resource routes so that their
| static paths are not captured by a `{product}` parameter.
|
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        Route::redirect('/', '/admin/products')->name('dashboard');

        Route::get('products/export', ProductExportController::class)->name('products.export');
        Route::delete('products/bulk', ProductBulkDeleteController::class)->name('products.bulk-destroy');

        Route::resource('products', ProductController::class)->except('show');
    });

/*
|--------------------------------------------------------------------------
| API documentation
|--------------------------------------------------------------------------
*/

Route::view('docs/api', 'docs.api')->name('docs.api');
