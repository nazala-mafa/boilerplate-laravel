<?php

use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware('auth')->group(function() {
    Route::apiResource('product', ProductController::class)->names('product');
    Route::get('user/select', [UserController::class, 'select'])->name('user.select');
    Route::apiResource('user', UserController::class)->names('user');

    Route::post('file/upload/{path}', [FileUploadController::class, 'store'])
        ->where('path', '.*')
        ->name('file-upload');
});