<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\StockMovementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Category
    Route::apiResource('categories', CategoryController::class);

    // Product
    Route::apiResource('products', ProductController::class);

    // Sale / Transaksi
    Route::apiResource('sales', SaleController::class);
    Route::get('sales/{sale}/receipt',[SaleController::class, 'receipt']);

    // Stock Movement
    Route::apiResource('stock-movements', StockMovementController::class);

});