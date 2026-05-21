<?php

use App\Http\Controllers\AdminPortfolioController;
use App\Http\Controllers\AdminSeoController;
use App\Http\Controllers\AdminStatisticsController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PortfolioPostController;
use App\Models\PortfolioPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/storage/{path}', function (string $path) {
    $path = ltrim(str_replace('\\', '/', $path), '/');
    $segments = explode('/', $path);

    if ($path === '' || in_array('..', $segments, true)) {
        abort(404);
    }

    $disk = Storage::disk('public');

    if (! $disk->fileExists($path)) {
        abort(404);
    }

    return $disk->response($path);
})->where('path', '.*')->name('storage.public');

Route::get('/', function () {
    return view('pages.welcome');
})->name('home');

Route::get('/apie-mane', function () {
    return view('pages.about-me');
})->name('about-me');

Route::get('/kontaktai', function () {
    return view('pages.contact');
})->name('contact');

Route::get('/web-sprendimai', function () {
    return view('pages.web-solutions');
})->name('web-solutions');

Route::get('/mobiliosios-aplikacijos', function () {
    return view('pages.mobile-apps');
})->name('mobile-apps');

Route::get('/grafika', [PortfolioPostController::class, 'index'])->name('graphics');
Route::get('/grafika/{portfolioPost}', [PortfolioPostController::class, 'show'])->name('graphics.show');

Route::prefix('en')->name('en.')->group(function () {
    Route::get('/', function () {
        return view('pages.welcome');
    })->name('home');

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

    Route::get('/graphics', [PortfolioPostController::class, 'index'])->name('graphics');
    Route::get('/graphics/{portfolioPost}', [PortfolioPostController::class, 'show'])->name('graphics.show');
});

Route::prefix('ru')->name('ru.')->group(function () {
    Route::get('/', function () {
        return view('pages.welcome');
    })->name('home');

    Route::get('/apie-mane', function () {
        return view('pages.about-me');
    })->name('about-me');

    Route::get('/kontaktai', function () {
        return view('pages.contact');
    })->name('contact');

    Route::get('/web-sprendimai', function () {
        return view('pages.web-solutions');
    })->name('web-solutions');

    Route::get('/mobiliosios-aplikacijos', function () {
        return view('pages.mobile-apps');
    })->name('mobile-apps');

    Route::get('/grafika', [PortfolioPostController::class, 'index'])->name('graphics');
    Route::get('/grafika/{portfolioPost}', [PortfolioPostController::class, 'show'])->name('graphics.show');
});

Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

Route::get('/sitemap.xml', function () {
    $paths = [
        '/',
        '/apie-mane',
        '/kontaktai',
        '/web-sprendimai',
        '/mobiliosios-aplikacijos',
        '/grafika',
        '/en',
        '/en/about-me',
        '/en/contact',
        '/en/web-solutions',
        '/en/mobile-apps',
        '/en/graphics',
        '/ru',
        '/ru/apie-mane',
        '/ru/kontaktai',
        '/ru/web-sprendimai',
        '/ru/mobiliosios-aplikacijos',
        '/ru/grafika',
    ];

    $portfolioPosts = PortfolioPost::query()
        ->orderBy('position')
        ->orderByDesc('created_at')
        ->get();

    foreach ($portfolioPosts as $portfolioPost) {
        $paths[] = route('graphics.show', $portfolioPost, false);
        $paths[] = route('en.graphics.show', $portfolioPost, false);
        $paths[] = route('ru.graphics.show', $portfolioPost, false);
    }

    $urls = collect($paths)
        ->unique()
        ->map(fn (string $path) => [
            'loc' => url($path),
            'lastmod' => now()->toDateString(),
        ]);

    return response()
        ->view('sitemap', ['urls' => $urls])
        ->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::get('/about-me', fn () => redirect('/en/about-me', 301));
Route::get('/contact', fn () => redirect('/en/contact', 301));
Route::get('/web-solutions', fn () => redirect('/en/web-solutions', 301));
Route::get('/mobile-apps', fn () => redirect('/en/mobile-apps', 301));
Route::get('/graphics', fn () => redirect('/en/graphics', 301));
Route::get('/graphics/{portfolioPost}', function (Request $request, PortfolioPost $portfolioPost) {
    return redirect()->route('en.graphics.show', ['portfolioPost' => $portfolioPost] + $request->query(), 301);
});
Route::get('/animations', function (Request $request) {
    return redirect()->route('graphics', $request->query(), 301);
});
Route::get('/animations/{portfolioPost}', function (Request $request, PortfolioPost $portfolioPost) {
    return redirect()->route('graphics.show', ['portfolioPost' => $portfolioPost] + $request->query(), 301);
});

Route::get('/admin', [LoginController::class, 'adminEntry'])->name('admin.dashboard');
Route::post('/admin', [LoginController::class, 'login'])->middleware('guest')->name('login');

Route::get('/login', fn () => redirect('/', 302));

Route::middleware(['auth', 'administrator'])->group(function () {
    Route::get('/admin/statistics', [AdminStatisticsController::class, 'index'])->name('admin.statistics');

    Route::get('/admin/seo', [AdminSeoController::class, 'edit'])->name('admin.seo.edit');
    Route::put('/admin/seo', [AdminSeoController::class, 'update'])->name('admin.seo.update');

    Route::get('/admin/portfolio', [AdminPortfolioController::class, 'index'])->name('admin.portfolio');
    Route::get('/admin/portfolio/create', [AdminPortfolioController::class, 'showCreateForm'])->name('admin.portfolio.create');
    Route::post('/admin/portfolio', [AdminPortfolioController::class, 'store'])->name('admin.portfolio.store');
    Route::get('/admin/portfolio/{portfolioPost}/edit', [AdminPortfolioController::class, 'showEditForm'])->name('admin.portfolio.edit');
    Route::put('/admin/portfolio/{portfolioPost}', [AdminPortfolioController::class, 'update'])->name('admin.portfolio.update');
    Route::patch('/admin/portfolio/{portfolioPost}/position', [AdminPortfolioController::class, 'updatePosition'])->name('admin.portfolio.position.update');
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
