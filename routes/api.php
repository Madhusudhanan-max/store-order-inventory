<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/history', [OrderController::class, 'history']);
Route::get('/products/low-stock', [ProductController::class, 'lowStock']);
