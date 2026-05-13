<?php

use App\Http\Controllers\AdminPortfolioController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PortfolioPostController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.welcome');
});

Route::get('/about-me', function () {
    return view('pages.about-me');
})->name('about-me');

Route::get('/contact', function () {
    return view('pages.contact');
})->name('contact');

Route::get('/web-solutions', function () {
    return view('pages.web-solutions');
})->name('web-solutions');

Route::get('/mobile-apps', function () {
    return view('pages.mobile-apps');
})->name('mobile-apps');

Route::get('/animations', [PortfolioPostController::class, 'index'])->name('animations');
Route::get('/animations/{portfolioPost}', [PortfolioPostController::class, 'show'])->name('animations.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware(['auth', 'administrator'])->group(function () {
    Route::get('/admin', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/admin/portfolio', [AdminPortfolioController::class, 'index'])->name('admin.portfolio');
    Route::get('/admin/portfolio/create', [AdminPortfolioController::class, 'showCreateForm'])->name('admin.portfolio.create');
    Route::post('/admin/portfolio', [AdminPortfolioController::class, 'store'])->name('admin.portfolio.store');
    Route::get('/admin/portfolio/{portfolioPost}/edit', [AdminPortfolioController::class, 'showEditForm'])->name('admin.portfolio.edit');
    Route::put('/admin/portfolio/{portfolioPost}', [AdminPortfolioController::class, 'update'])->name('admin.portfolio.update');
    Route::patch('/admin/portfolio/{portfolioPost}/move-up', [AdminPortfolioController::class, 'moveUp'])->name('admin.portfolio.move-up');
    Route::patch('/admin/portfolio/{portfolioPost}/move-down', [AdminPortfolioController::class, 'moveDown'])->name('admin.portfolio.move-down');
    Route::delete('/admin/portfolio/{portfolioPost}/thumbnail', [AdminPortfolioController::class, 'destroyThumbnail'])->name('admin.portfolio.thumbnail.destroy');
    Route::delete('/admin/portfolio/{portfolioPost}/post-image', [AdminPortfolioController::class, 'destroyPostImage'])->name('admin.portfolio.post-image.destroy');
    Route::delete('/admin/portfolio/{portfolioPost}', [AdminPortfolioController::class, 'destroy'])->name('admin.portfolio.destroy');

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
