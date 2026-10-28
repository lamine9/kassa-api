<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\PurchaseController;
use App\Http\Controllers\Api\V1\StockMovementController;
use App\Http\Controllers\Api\V1\SupplierController;
use App\Http\Controllers\Api\V1\SupplierReturnController;
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

        // Customers
        Route::get('/customers', [CustomerController::class, 'index'])
            ->middleware('permission:customers.view');

        Route::post('/customers', [CustomerController::class, 'store'])
            ->middleware('permission:customers.create');

        Route::get('/customers/{customer}', [CustomerController::class, 'show'])
            ->middleware('permission:customers.view');

        Route::put('/customers/{customer}', [CustomerController::class, 'update'])
            ->middleware('permission:customers.update');

        Route::patch('/customers/{customer}', [CustomerController::class, 'update'])
            ->middleware('permission:customers.update');

        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])
            ->middleware('permission:customers.delete');

        // Suppliers
        Route::get('/suppliers', [SupplierController::class, 'index'])
            ->middleware('permission:suppliers.view');

        Route::post('/suppliers', [SupplierController::class, 'store'])
            ->middleware('permission:suppliers.create');

        Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])
            ->middleware('permission:suppliers.view');

        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])
            ->middleware('permission:suppliers.update');

        Route::patch('/suppliers/{supplier}', [SupplierController::class, 'update'])
            ->middleware('permission:suppliers.update');

        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])
            ->middleware('permission:suppliers.delete');

        // Achats
        Route::get('/purchases', [PurchaseController::class, 'index'])
            ->middleware('permission:purchases.view');

        Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])
            ->middleware('permission:purchases.view');

        Route::put('/purchases/{purchase}', [PurchaseController::class, 'update'])
            ->middleware('permission:purchases.update');

        Route::patch('/purchases/{purchase}', [PurchaseController::class, 'update'])
            ->middleware('permission:purchases.update');

        Route::post('/purchases', [PurchaseController::class, 'store'])
            ->middleware('permission:purchases.create');

        Route::delete('/purchases/{purchase}', [PurchaseController::class, 'destroy'])
            ->middleware('permission:purchases.delete');

        Route::post('/purchases/{purchase}/receive', [PurchaseController::class, 'receive'])
            ->middleware('permission:purchases.receive');

        // Retours fournisseurs
        Route::prefix('supplier-returns')
            ->controller(SupplierReturnController::class)
            ->group(function () {
                Route::get('/', 'index')->middleware('permission:supplier_returns.view');
                Route::post('/', 'store')->middleware('permission:supplier_returns.create');

                Route::get('/{supplierReturn}', 'show')
                    ->middleware('permission:supplier_returns.view');

                Route::put('/{supplierReturn}', 'update')
                    ->middleware('permission:supplier_returns.update');

                Route::patch('/{supplierReturn}', 'update')
                    ->middleware('permission:supplier_returns.update');

                Route::post('/{supplierReturn}/validate', 'validateReturn')
                    ->middleware('permission:supplier_returns.validate');

                Route::post('/{supplierReturn}/cancel', 'cancel')
                    ->middleware('permission:supplier_returns.cancel');
            });

    });

     //demo
    //20|7Bd2K3LAI8cMFOBYcdE36ze6znAn7oI3JSvGHqXufa1f2ad6

    //B
    //23|3XGWRDWfz8PYRLNBIbC0vb0D7kElwqCk6qCLDPitccc5cc20


});
