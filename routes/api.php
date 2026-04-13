<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\ReportsController;
use App\Http\Controllers\Api\TenantRegistrationController;
use App\Http\Controllers\Api\UserController;

// Public
Route::get('/health', HealthController::class);
Route::post('/register-tenant', [TenantRegistrationController::class, 'register']);
Route::post('/auth/register', [TenantRegistrationController::class, 'register']);
Route::middleware('tenant.identify')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
});

// Protected
Route::middleware(['throttle:api', 'auth:sanctum', 'tenant.identify', 'tenant.user', 'subscription'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/stats', [DashboardController::class, 'index']);
    Route::get('/reports', [ReportsController::class, 'index']);

    Route::apiResource('categories', CategoryController::class);
    Route::post('/products', [ProductController::class, 'store'])->middleware('tenant.plan:products');
    Route::apiResource('products', ProductController::class)->except(['store']);
    Route::get('/products/low-stock', [ProductController::class, 'lowStock']);
    Route::post('/products/{product}/adjust-stock', [ProductController::class, 'adjustStock']);

    Route::apiResource('sales', SaleController::class)->only(['index', 'store', 'show']);
    Route::get('/sales/daily-report', [SaleController::class, 'dailyReport']);
    Route::get('/sales/monthly-report', [SaleController::class, 'monthlyReport']);
    Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel']);

    Route::apiResource('customers', CustomerController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('/customers/{customer}/purchase-history', [CustomerController::class, 'purchaseHistory']);

    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::post('/inventory/adjust', [InventoryController::class, 'adjust']);
    Route::get('/inventory/history', [InventoryController::class, 'history']);

    Route::post('/users', [UserController::class, 'store'])->middleware('tenant.plan:users');
    Route::apiResource('users', UserController::class)->only(['index', 'update', 'destroy']);
    Route::put('/users/{user}/role', [UserController::class, 'update']);
});

