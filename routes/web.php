<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\Shop\ShopController;
use App\Http\Controllers\Shop\OrderController;

use App\Http\Controllers\Shop\QuestionController as ShopQuestionController;
use App\Http\Controllers\QuestionController as AdminQuestionController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/items/{item}', [ShopController::class, 'show'])->name('shop.items.show');

/**
 * USER: post question in item page
 */
Route::post('/shop/items/{item}/questions', [ShopQuestionController::class, 'store'])
    ->middleware(['auth', 'role:user'])
    ->name('questions.store');

/**
 * USER: My Questions
 */
Route::get('/questions', [ShopQuestionController::class, 'myQuestions'])
    ->middleware(['auth', 'role:user'])
    ->name('questions.index');

Route::get('/my-questions', function () {
    return redirect()->route('questions.index');
})->middleware(['auth', 'role:user'])->name('questions.mine');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/questions/{question}/report', [ReportController::class, 'create'])
        ->name('reports.create');
    Route::post('/questions/{question}/report', [ReportController::class, 'store'])
        ->name('reports.store');
});



/**
 * SUPERADMIN
 */
Route::middleware(['auth', 'role:superadmin'])->group(function () {

    // admins
    Route::get('/superadmin/create-admin', [SuperAdminController::class, 'createAdmin'])
        ->name('superadmin.create-admin');
    Route::post('/superadmin/store-admin', [SuperAdminController::class, 'storeAdmin'])
        ->name('superadmin.store-admin');

    Route::get('/superadmin/admins/{user}/edit', [SuperAdminController::class, 'editAdmin'])
        ->name('superadmin.admins.edit');
    Route::patch('/superadmin/admins/{user}', [SuperAdminController::class, 'updateAdmin'])
        ->name('superadmin.admins.update');
    Route::delete('/superadmin/admins/{user}', [SuperAdminController::class, 'destroyAdmin'])
        ->name('superadmin.admins.destroy');

    // users
    Route::get('/superadmin/create-user', [SuperAdminController::class, 'createUser'])
        ->name('superadmin.create-user');
    Route::post('/superadmin/store-user', [SuperAdminController::class, 'storeUser'])
        ->name('superadmin.store-user');

    Route::get('/superadmin/users/{user}/edit', [SuperAdminController::class, 'editUser'])
        ->name('superadmin.users.edit');
    Route::patch('/superadmin/users/{user}', [SuperAdminController::class, 'updateUser'])
        ->name('superadmin.users.update');
    Route::delete('/superadmin/users/{user}', [SuperAdminController::class, 'destroyUser'])
        ->name('superadmin.users.destroy');

    // reports
    Route::get('/superadmin/reports', [SuperAdminController::class, 'reports'])
        ->name('superadmin.reports');
    Route::patch('/superadmin/reports/{report}/resolve', [SuperAdminController::class, 'resolve'])
        ->name('superadmin.reports.resolve');
    Route::patch('/superadmin/reports/{report}/dismiss', [SuperAdminController::class, 'dismiss'])
        ->name('superadmin.reports.dismiss');
});

/**
 * ADMIN
 */
Route::middleware(['auth', 'role:admin'])->group(function () {

    // orders
    Route::get('/admin/orders', [AdminController::class, 'index'])
        ->name('admin.orders.index');
    Route::patch('/admin/orders/{order}/ship', [AdminController::class, 'ship'])
        ->name('admin.orders.ship');
    Route::patch('/admin/orders/{order}/approve-refund', [AdminController::class, 'approveRefund'])
        ->name('admin.orders.approve-refund');

    // items
        // Item Management Dashboard
    Route::get('/admin/items', [AdminController::class, 'indexItems'])
        ->name('admin.items.index');

    Route::get('/admin/items/create', [AdminController::class, 'createItem'])
        ->name('admin.items.create');
    Route::post('/admin/items', [AdminController::class, 'storeItem'])
        ->name('admin.items.store');

    /**
     * ADMIN: Pending Questions
     **/
    Route::get('/admin/questions', [AdminQuestionController::class, 'index'])
        ->name('admin.questions.index');

    Route::get('/admin/pending-questions', function () {
        return redirect()->route('admin.questions.index');
    })->name('admin.questions');

    Route::patch('/admin/questions/{question}/answer', [AdminQuestionController::class, 'answer'])
        ->name('admin.questions.answer');
    Route::delete('/admin/questions/{question}/answer', [AdminQuestionController::class, 'deleteAnswer'])
        ->name('admin.questions.answer.delete');
        // Edit & Update
    Route::get('/admin/items/{item}/edit', [AdminController::class, 'editItem'])
        ->name('admin.items.edit');
    Route::patch('/admin/items/{item}', [AdminController::class, 'updateItem'])
        ->name('admin.items.update');

        // Actions
    Route::patch('/admin/items/{item}/toggle', [AdminController::class, 'toggleItem'])->name('admin.items.toggle');
    Route::delete('/admin/items/{item}', [AdminController::class, 'destroyItem'])->name('admin.items.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');

    Route::post('/shop/items/{item}/order', [OrderController::class, 'store'])->name('orders.store');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/{order}/refund', [OrderController::class, 'requestRefund'])->name('orders.refund.request');
});

require __DIR__ . '/auth.php';
