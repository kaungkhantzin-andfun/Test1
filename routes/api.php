<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserRegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/register', [UserRegisterController::class, 'register']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    
    // Role-specific profile routes
    Route::get('/admin/profile', [ProfileController::class, 'adminProfile']);
    Route::get('/astrology/profile', [ProfileController::class, 'astrologyProfile']);
    Route::get('/customer/profile', [ProfileController::class, 'customerProfile']);
});