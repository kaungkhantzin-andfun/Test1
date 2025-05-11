<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserRegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\QuestionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/register', [UserRegisterController::class, 'register']);
Route::post('/send-otp', [UserRegisterController::class, 'sendOtp']);
Route::post('/verify-otp', [UserRegisterController::class, 'verifyOtp']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/user/edit', [UserRegisterController::class, 'useredit']);
    // Role-specific profile routes
    Route::get('/admin/profile', [ProfileController::class, 'adminProfile']);
    Route::get('/astrology/profile', [ProfileController::class, 'astrologyProfile']);
    Route::get('/customer/profile', [ProfileController::class, 'customerProfile']);

     Route::post('/questions', [QuestionController::class, 'create']);
});