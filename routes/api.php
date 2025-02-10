<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\SellerMiddleware;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;

Route::post('/register/consumer', [AuthController::class, 'register']);
Route::post('/login/consumer', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function() {
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::post('/register/seller', [AuthController::class, 'registerSeller']);

    //product controller
    Route::get('/product', [ProductController::class, 'index']);
    Route::get('/product/{id}', [ProductController::class, 'show']);
    Route::middleware('role:seller')->group(function() {
        Route::post('/product', [ProductController::class, 'store']);

        Route::middleware(SellerMiddleware::class)->group(function() {
            Route::delete('/product/{id}', [ProductController::class, 'destroy']);
            Route::patch('/product/{id}', [ProductController::class, 'update']);
        });
    });

    Route::middleware('role:admin')->group(function() {
        Route::patch('/verify/product/{id}', [AdminController::class, 'verifyProduct']);
        Route::post('/category', [CategoryController::class, 'store']);
    });
});
