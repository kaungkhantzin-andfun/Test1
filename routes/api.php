<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserRegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\FreeBaydinController;
use App\Http\Controllers\BadyinToaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AstrologerController;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/register', [UserRegisterController::class, 'register']);


    Route::get('/astrologers', [AstrologerController::class, 'index']);  
    Route::post('/astrologers', [AstrologerController::class, 'store']); 
    Route::get('/astrologers/{id}', [AstrologerController::class, 'show']); 
    Route::put('/astrologers/{id}', [AstrologerController::class, 'update']); 
    Route::delete('/astrologers/{id}', [AstrologerController::class, 'destroy']); 

Route::post('/freebaydin/search', [FreeBaydinController::class, 'search']);

Route::post('/send-otp', [UserRegisterController::class, 'sendOtp']);
Route::post('/verify-otp', [UserRegisterController::class, 'verifyOtp']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/free-baydins-create', [FreeBaydinController::class, 'store']);

    Route::get('/free-baydins', [FreeBaydinController::class, 'index'])->name('free-baydin.index');
    Route::post('/free-baydins-create', [FreeBaydinController::class, 'store'])->name('free-baydin.store');
    Route::get('/free-baydins/{id}', [FreeBaydinController::class, 'show'])->name('free-baydin.show');
    Route::put('/free-baydins/{id}', [FreeBaydinController::class, 'update'])->name('free-baydin.update');
    Route::delete('/free-baydins/{id}', [FreeBaydinController::class, 'destroy'])->name('free-baydin.destroy');
    Route::post('/user/edit', [UserRegisterController::class, 'useredit']);
    // Role-specific profile routes
    Route::get('/admin/profile', [ProfileController::class, 'adminProfile']);
    Route::get('/astrology/profile', [ProfileController::class, 'astrologyProfile']);
    Route::get('/customer/profile', [ProfileController::class, 'customerProfile']);

     Route::post('/questions', [QuestionController::class, 'create']);



     Route::post('/toasks', [BadyinToaskController::class, 'create']);
     
     // Money input endpoint
     Route::post('/money/input', [UserRegisterController::class, 'moneyinput']);
     Route::get('/toasks', [BadyinToaskController::class, 'index']);
     Route::get('/toasks/{id}', [BadyinToaskController::class, 'show']);
     Route::put('/toasks/{id}', [BadyinToaskController::class, 'update']);
     Route::delete('/toasks/{id}', [BadyinToaskController::class, 'destroy']);

    // Astrologer specific routes
    Route::get('/available-astrologers', [BadyinToaskController::class, 'getAvailableAstrologers']);
    Route::get('/astrologer-tasks/{astrologerId}', [BadyinToaskController::class, 'getAstrologerTasks']);
    Route::patch('/toasks/{id}/status', [BadyinToaskController::class, 'updateTaskStatus']);


    ///////////money

     Route::post('/user/moneyinput', [UserRegisterController::class, 'moneyinput']);
     Route::get('/user/moneyinput_list', [UserRegisterController::class, 'moneyinputlist']);


     Route::post('/user/moneyapprove', [UserRegisterController::class, 'moneyapprove']);


     Route::get('/user/user_money_list', [UserRegisterController::class, 'user_money_list']);

     Route::get('/user/banktyle', [UserRegisterController::class, 'banktyle']);
});

