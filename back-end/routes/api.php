<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Authentication Flow (Two-Factor)
Route::post('/login/request-otp', [AuthController::class, 'requestLogin']);
Route::post('/login/verify-otp', [AuthController::class, 'verifyLogin']);

// Password Management (Unauthenticated)
Route::post('/password/forgot', [PasswordController::class, 'forgotPassword']);
Route::post('/password/reset', [PasswordController::class, 'resetPassword']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/password/change', [PasswordController::class, 'changePassword']);
    
    // Admin Routes
    Route::middleware('role:admin')->group(function () {
        
    });

    // Teacher Routes
    Route::middleware('role:teacher')->group(function () {
        
    });

    // Student Routes
    Route::middleware('role:student')->group(function () {
        
    });

});
