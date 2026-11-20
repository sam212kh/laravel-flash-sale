<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/products', [\App\Http\Controllers\Api\ProductController::class, 'index']);
Route::post('/products/{product}/reserve', [\App\Http\Controllers\Api\ReservationController::class, 'store'])
    ->whereNumber('product');
Route::post('/orders', [\App\Http\Controllers\Api\OrderController::class, 'store']);
