<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\SuperAdminController;
use App\Http\Controllers\Api\UserQuestionController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    Route::middleware('api.auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::patch('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

/*
|--------------------------------------------------------------------------
| Public / Optional Auth
|--------------------------------------------------------------------------
*/
Route::middleware('api.optional_auth')->group(function () {
    Route::get('/items', [ShopController::class, 'index']);
    Route::get('/items/{item}', [ShopController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| User
|--------------------------------------------------------------------------
*/
Route::middleware(['api.auth', 'role:user'])->group(function () {
    // Addresses
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::patch('/addresses/{address}', [AddressController::class, 'update']);
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy']);

    // Questions
    Route::get('/questions', [UserQuestionController::class, 'index']);
    Route::post('/items/{item}/questions', [UserQuestionController::class, 'store']);
    Route::post('/questions/{question}/report', [UserQuestionController::class, 'report']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::patch('/orders/{order}/refund', [OrderController::class, 'requestRefund']);

    // Wallet
    Route::get('/wallet', [WalletController::class, 'index']);
    Route::post('/wallet/top-up', [WalletController::class, 'topUp']);
});

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/
Route::middleware(['api.auth', 'role:admin'])->prefix('admin')->group(function () {
    // Items
    Route::get('/items', [AdminController::class, 'items']);
    Route::get('/items/{item}', [AdminController::class, 'showItem']);
    Route::post('/items', [AdminController::class, 'storeItem']);
    Route::patch('/items/{item}', [AdminController::class, 'updateItem']);
    Route::delete('/items/{item}', [AdminController::class, 'destroyItem']);
    Route::patch('/items/{item}/toggle', [AdminController::class, 'toggleItem']);

    // Orders
    Route::get('/orders', [AdminController::class, 'orders']);
    Route::get('/orders/{order}', [AdminController::class, 'showAdminOrder']);
    Route::patch('/orders/{order}/ship', [AdminController::class, 'ship']);
    Route::patch('/orders/{order}/approve-refund', [AdminController::class, 'approveRefund']);
    Route::patch('/orders/{order}/dismiss-refund', [AdminController::class, 'dismissRefund']);

    // Questions
    Route::get('/questions', [AdminController::class, 'questions']);
    Route::patch('/questions/{question}/answer', [AdminController::class, 'answerQuestion']);
    Route::delete('/questions/{question}/answer', [AdminController::class, 'deleteAnswer']);
});

/*
|--------------------------------------------------------------------------
| SuperAdmin
|--------------------------------------------------------------------------
*/
Route::middleware(['api.auth', 'role:superadmin'])->prefix('superadmin')->group(function () {
    // Admins
    Route::get('/admins', [SuperAdminController::class, 'admins']);
    Route::post('/admins', [SuperAdminController::class, 'storeAdmin']);
    Route::patch('/admins/{user}', [SuperAdminController::class, 'updateAdmin']);
    Route::delete('/admins/{user}', [SuperAdminController::class, 'destroyAdmin']);
    Route::patch('/admins/{userId}/restore', [SuperAdminController::class, 'restoreAdmin']);

    // Users
    Route::get('/users', [SuperAdminController::class, 'users']);
    Route::post('/users', [SuperAdminController::class, 'storeUser']);
    Route::patch('/users/{user}', [SuperAdminController::class, 'updateUser']);
    Route::delete('/users/{user}', [SuperAdminController::class, 'destroyUser']);
    Route::patch('/users/{userId}/restore', [SuperAdminController::class, 'restoreUser']);

    // Reports
    Route::get('/reports', [SuperAdminController::class, 'reports']);
    Route::patch('/reports/{report}/resolve', [SuperAdminController::class, 'resolve']);
    Route::patch('/reports/{report}/dismiss', [SuperAdminController::class, 'dismiss']);
});
