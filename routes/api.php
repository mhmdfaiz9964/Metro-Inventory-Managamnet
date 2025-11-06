<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\BomComponentController;
use App\Http\Controllers\Api\ProductController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/bom-components', [BomComponentController::class, 'index']);
Route::post('/bom-components', [BomComponentController::class, 'store']);

Route::get('/suppliers', [SupplierController::class, 'index'])->name('admin.api-suppliers.index');
Route::post('/suppliers', [SupplierController::class, 'store'])->name('admin.api-suppliers.store');

Route::get('/customers', [CustomerController::class, 'index'])->name('admin.api-customers.index');
Route::post('/customers', [CustomerController::class, 'store'])->name('admin.api-customers.store');

Route::get('/products', [ProductController::class, 'index'])->name('admin.api-products.index');
Route::post('/products', [ProductController::class, 'store'])->name('admin.api-products.store');