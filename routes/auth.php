<?php

use App\Http\Controllers\Auth\UserController;
use Illuminate\Support\Facades\Route;

// Routes for guests (not authenticated)
Route::middleware('guest')->group(function () {
    Route::get('register', [UserController::class, 'createRegister'])
                ->name('register');

    Route::post('register', [UserController::class, 'storeRegister']);

    Route::get('login', [UserController::class, 'createLogin'])
                ->name('login');

    Route::post('login', [UserController::class, 'storeLogin']);
});

// Routes for authenticated users
Route::middleware('auth')->group(function () {
    Route::get('confirm-password', [UserController::class, 'show'])
                ->name('password.confirm');

    Route::post('confirm-password', [UserController::class, 'store']);

    Route::post('logout', [UserController::class, 'destroy'])
                ->name('logout');


});
