<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('products', [\App\Http\Controllers\ProductController::class, 'index']);
Route::get('services', [\App\Http\Controllers\ServiceController::class, 'index']);

