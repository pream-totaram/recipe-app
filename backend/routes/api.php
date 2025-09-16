<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('test', function () {
    return "Hello, World!";
});

Route::post('login', [AuthController::class, 'authenticate'])
    ->name('login');

Route::post('/register', [AuthController::class,'register'])
    ->middleware('guest')
    ->name('register');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
