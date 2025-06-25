<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login'])
    ->name('api.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('products', \App\Http\Controllers\Api\ProductController::class);
    Route::get('products/barcode/{barcode}', [\App\Http\Controllers\Api\ProductController::class, 'showByBarcode'])
        ->name('api.products.showByBarcode');

    Route::apiResource('payment-methods', \App\Http\Controllers\Api\PaymentMethodController::class);

    Route::apiResource('orders', \App\Http\Controllers\Api\OrderController::class);
    Route::get('settings', [\App\Http\Controllers\Api\SettingController::class, 'index'])
        ->name('api.settings.index');
});
