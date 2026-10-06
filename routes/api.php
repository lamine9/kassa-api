<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\StockMovementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UnitController;

Route::prefix('v1')->group(function () {

    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        //Units
        Route::get('/units', [UnitController::class, 'index'])
            ->middleware('permission:units.view');

        Route::post('/units', [UnitController::class, 'store'])
            ->middleware('permission:units.create');

        Route::get('/units/{unit}', [UnitController::class, 'show'])
            ->middleware('permission:units.view');

        Route::put('/units/{unit}', [UnitController::class, 'update'])
            ->middleware('permission:units.update');

        Route::patch('/units/{unit}', [UnitController::class, 'update'])
            ->middleware('permission:units.update');

        Route::delete('/units/{unit}', [UnitController::class, 'destroy'])
            ->middleware('permission:units.delete');

        //Categories
        Route::get('/categories', [CategoryController::class, 'index'])
            ->middleware('permission:categories.view');
        Route::post('/categories', [CategoryController::class, 'store'])
            ->middleware('permission:categories.create');
        Route::get('/categories/{category}', [CategoryController::class, 'show'])
            ->middleware('permission:categories.view');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])
            ->middleware('permission:categories.update');
        Route::patch('/categories/{category}', [CategoryController::class, 'update'])
            ->middleware('permission:categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])
            ->middleware('permission:categories.delete');

        // Products
        Route::get('/products', [ProductController::class, 'index'])
            ->middleware('permission:products.view');

        Route::post('/products', [ProductController::class, 'store'])
            ->middleware('permission:products.create');

        Route::get('/products/{product}', [ProductController::class, 'show'])
            ->middleware('permission:products.view');

        Route::put('/products/{product}', [ProductController::class, 'update'])
            ->middleware('permission:products.update');

        Route::patch('/products/{product}', [ProductController::class, 'update'])
            ->middleware('permission:products.update');

        Route::delete('/products/{product}', [ProductController::class, 'destroy'])
            ->middleware('permission:products.delete');

        // Stock Movements
        // Stock actuel du produit
        Route::get('/products/{product}/stock', [ProductController::class, 'stock'])
            ->middleware('permission:products.view');

        Route::get('/stock-movements', [StockMovementController::class, 'index'])
            ->middleware('permission:stock.view');

        Route::post('/stock-movements', [StockMovementController::class, 'store'])
            ->middleware('permission:stock.create');
    });
     //demo
    //20|7Bd2K3LAI8cMFOBYcdE36ze6znAn7oI3JSvGHqXufa1f2ad6

    //B
    //23|3XGWRDWfz8PYRLNBIbC0vb0D7kElwqCk6qCLDPitccc5cc20


});
