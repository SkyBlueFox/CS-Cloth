<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\Shop\ShopController;
use App\Http\Controllers\Shop\OrderController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/items/{item}', [ShopController::class, 'show'])->name('shop.items.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'role:superadmin'])->group(function () {
    Route::get('/superadmin/create-admin', [SuperAdminController::class, 'createAdmin'])
        ->name('superadmin.create');
    Route::post('/superadmin/store-admin', [SuperAdminController::class, 'storeAdmin'])
        ->name('superadmin.store');
    Route::get('/superadmin/reports', [SuperAdminController::class, 'reports']
    )->name('superadmin.reports');
    Route::patch('/superadmin/reports/{report}/resolve', [SuperAdminController::class, 'resolve'])
        ->name('superadmin.reports.resolve');
    Route::patch('/superadmin/reports/{report}/dismiss', [SuperAdminController::class, 'dismiss'])
        ->name('superadmin.reports.dismiss');
});

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/admin/orders', [AdminController::class, 'index'])
        ->name('admin.orders.index');
    Route::patch('/admin/orders/{order}/ship', [AdminController::class, 'ship'])
        ->name('admin.orders.ship');
    Route::patch('/admin/orders/{order}/approve-refund', [AdminController::class, 'approveRefund'])
        ->name('admin.orders.approve-refund');
});

Route::post('/questions/{id}/report', [ReportController::class, 'store'])
    ->middleware(['auth', 'verified', 'role:user'])
    ->name('reports.store');


Route::middleware(['auth'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');

    Route::post('/shop/items/{item}/order', [OrderController::class, 'store'])->name('orders.store');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/{order}/refund', [OrderController::class, 'requestRefund'])->name('orders.refund.request');
});

require __DIR__.'/auth.php';
