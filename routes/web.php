<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware(['auth', 'administrator'])->group(function () {
    Route::get('/admin', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users');
    Route::get('/admin/users/create', [AdminUserController::class, 'showCreateForm'])->name('admin.users.create');
    Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{user}/edit', [AdminUserController::class, 'showEditForm'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
