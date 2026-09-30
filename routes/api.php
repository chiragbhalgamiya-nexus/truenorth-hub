<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Api\Auth\AdminAuthController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

// Customer Authentication Routes (throttled)
Route::post('/auth/register', [AuthController::class, 'register'])
    ->middleware('throttle:10,1')
    ->name('auth.register');

Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('auth.login');

// Customer Protected Routes (requires Sanctum auth)
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/auth/logout', [AuthController::class, 'logout'])
        ->name('auth.logout');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])
        ->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'changePassword'])
        ->name('profile.change-password');

    // Addresses
    Route::get('/addresses', [AddressController::class, 'index'])
        ->name('addresses.index');
    Route::post('/addresses', [AddressController::class, 'store'])
        ->name('addresses.store');
    Route::get('/addresses/{address}', [AddressController::class, 'show'])
        ->name('addresses.show');
    Route::put('/addresses/{address}', [AddressController::class, 'update'])
        ->name('addresses.update');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])
        ->name('addresses.destroy');
    Route::put('/addresses/{address}/set-default', [AddressController::class, 'setDefault'])
        ->name('addresses.set-default');
});

// Admin Authentication Routes (throttled)
Route::post('/admin/login', [AdminAuthController::class, 'login'])
    ->middleware('throttle:3,1')
    ->name('admin.login');

// Admin Protected Routes (requires admin role)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'logout'])
        ->name('logout');

    // Customer Management
    Route::get('/customers', [AdminCustomerController::class, 'index'])
        ->name('customers.index');
    Route::get('/customers/{customer}', [AdminCustomerController::class, 'show'])
        ->name('customers.show');
    Route::put('/customers/{customer}/activate', [AdminCustomerController::class, 'activate'])
        ->name('customers.activate');
    Route::put('/customers/{customer}/deactivate', [AdminCustomerController::class, 'deactivate'])
        ->name('customers.deactivate');
});
