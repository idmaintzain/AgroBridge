<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MarketController::class, 'index'])->name('market');
Route::view('/about', 'pages.about')->name('about');
Route::view('/how-it-works', 'pages.how')->name('how');
Route::view('/for-farmers', 'pages.farmers')->name('farmers');
Route::view('/for-buyers', 'pages.buyers')->name('buyers');
Route::view('/contact', 'pages.contact')->name('contact');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/demo/{role}', [AuthController::class, 'demo'])->name('demo.login');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/desk', [OfficeController::class, 'desk'])->name('desk');
    Route::get('/desk/listings', [OfficeController::class, 'listings'])->name('office.listings');
    Route::get('/desk/wallet', [OfficeController::class, 'wallet'])->name('office.wallet');
    Route::post('/desk/wallet/fund', [WalletController::class, 'fund'])->name('wallet.fund');
    Route::post('/desk/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw');
    Route::post('/desk/wallet/transfer', [WalletController::class, 'transfer'])->name('wallet.transfer');

    Route::get('/listings/create', [ListingController::class, 'create'])->name('listings.create');
    Route::post('/listings', [ListingController::class, 'store'])->name('listings.store');
    Route::get('/listings/{listing}/edit', [ListingController::class, 'edit'])->name('listings.edit');
    Route::put('/listings/{listing}', [ListingController::class, 'update'])->name('listings.update');

    Route::get('/purchases', [OrderController::class, 'purchases'])->name('purchases');
    Route::get('/sales', [OrderController::class, 'sales'])->name('sales');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/listings/{listing}/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::post('/orders/{order}/pay', [OrderController::class, 'pay'])->name('orders.pay');
    Route::post('/orders/{order}/confirm', [OrderController::class, 'confirm'])->name('orders.confirm');
    Route::post('/orders/{order}/code', [OrderController::class, 'code'])->name('orders.code');
    Route::post('/orders/{order}/dispute', [OrderController::class, 'dispute'])->name('orders.dispute');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/disputes', [AdminController::class, 'disputes'])->name('admin.disputes');
    Route::get('/people', [AdminController::class, 'people'])->name('admin.people');
    Route::get('/orders', [AdminController::class, 'orders'])->name('admin.orders');
    Route::post('/orders/{order}/resolve', [AdminController::class, 'resolve'])->name('admin.orders.resolve');
    Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
    Route::post('/settle', [AdminController::class, 'settle'])->name('admin.settle');
});

Route::get('/listings/{listing}', [ListingController::class, 'show'])->name('listings.show');
