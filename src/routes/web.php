<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
//    return view('welcome');
//});

Route::get('/', fn() => redirect()->route('reservations.index'));

Route::resource('reservations', \App\Http\Controllers\ReservationController::class)
    ->only(['index','create','store','show']);

Route::get('/reservations/export/csv', [\App\Http\Controllers\ReservationExportController::class, 'index'])
    ->name('reservations.export.csv');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


Route::middleware('auth')->group(function () {
    Route::get('/', fn() => redirect()->route('reservations.index'));
    Route::resource('reservations', \App\Http\Controllers\ReservationController::class)->only(['index','create','store','show']);
    Route::get('reservations-export', [\App\Http\Controllers\ReservationExportController::class, 'index'])->name('reservations.export');

    Route::post('reservations/price-preview', [\App\Http\Controllers\ReservationController::class,'pricePreview'])
        ->name('reservations.price-preview');

    // マスタ管理
    Route::resource('campaigns', \App\Http\Controllers\CampaignController::class)->except(['show']);
    Route::resource('products', \App\Http\Controllers\ProductController::class)->except(['show']);
    Route::resource('stores', \App\Http\Controllers\StoreController::class)->except(['show']);

    Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->except(['show']);
    Route::get('roles', [\App\Http\Controllers\Admin\RoleController::class, 'index'])->name('roles.index');

    Route::resource('early-birds', \App\Http\Controllers\Admin\EarlyBirdDiscountController::class)->except(['show']);
});
