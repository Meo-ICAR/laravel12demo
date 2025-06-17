<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanyController;
use Illuminate\Support\Facades\Route;

// Redirect root to login if not authenticated
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', function () {
        return view('auth.login');
    })->name('login');

    Route::get('register', function () {
        return view('auth.register');
    })->name('register');

    Route::get('forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');
});

// Protected Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Roles and Permissions Routes
    Route::middleware(['permission:role_management'])->group(function () {
        Route::resource('roles', RoleController::class);
    });

    Route::middleware(['permission:permission_management'])->group(function () {
        Route::resource('permissions', PermissionController::class);
    });

    // User Management Routes
    Route::middleware(['permission:user_management'])->group(function () {
        Route::resource('users', UserController::class);
        Route::get('users/trashed', [UserController::class, 'trashed'])->name('users.trashed');
        Route::post('users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
        Route::delete('users/{id}/force-delete', [UserController::class, 'forceDelete'])->name('users.force-delete');
    });

    // Company routes
    Route::resource('companies', CompanyController::class);
    Route::get('companies/trashed', [CompanyController::class, 'trashed'])->name('companies.trashed');
    Route::post('companies/{id}/restore', [CompanyController::class, 'restore'])->name('companies.restore');
    Route::delete('companies/{id}/force-delete', [CompanyController::class, 'forceDelete'])->name('companies.force-delete');
});

require __DIR__.'/auth.php';
