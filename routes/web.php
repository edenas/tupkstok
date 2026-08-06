<?php

use App\Http\Controllers\AdminBlogController;
use App\Http\Controllers\AdminMediaController;
use App\Http\Controllers\AdminSeoController;
use App\Http\Controllers\AdminStatisticsController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminWordPressMigrationController;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SitemapController;
use App\Models\BlogPost;
use App\Models\WordPressImportRedirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
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

Route::get('/kontaktai', function () {
    return view('pages.contact');
})->name('contact');

Route::get('/blogas', [BlogPostController::class, 'index'])->name('blog');
Route::get('/blogas/{blogPost:slug}', [BlogPostController::class, 'show'])->name('blog.show');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::get('/en', fn () => redirect('/', 301));
Route::get('/en/contact', fn () => redirect('/kontaktai', 301));
Route::get('/en/graphics', fn () => redirect('/blogas', 301));
Route::get('/en/graphics/{blogPost:slug}', function (Request $request, BlogPost $blogPost) {
    return redirect()->route('blog.show', ['blogPost' => $blogPost] + $request->query(), 301);
});
Route::get('/ru', fn () => redirect('/', 301));
Route::get('/ru/kontaktai', fn () => redirect('/kontaktai', 301));
Route::get('/ru/grafika', fn () => redirect('/blogas', 301));
Route::get('/ru/grafika/{blogPost:slug}', function (Request $request, BlogPost $blogPost) {
    return redirect()->route('blog.show', ['blogPost' => $blogPost] + $request->query(), 301);
});
Route::get('/contact', fn () => redirect('/kontaktai', 301));
Route::get('/grafika', fn (Request $request) => redirect()->route('blog', $request->query(), 301));
Route::get('/grafika/{blogPost:slug}', function (Request $request, BlogPost $blogPost) {
    return redirect()->route('blog.show', ['blogPost' => $blogPost] + $request->query(), 301);
});
Route::get('/graphics', fn () => redirect('/blogas', 301));
Route::get('/graphics/{blogPost:slug}', function (Request $request, BlogPost $blogPost) {
    return redirect()->route('blog.show', ['blogPost' => $blogPost] + $request->query(), 301);
});
Route::get('/animations', function (Request $request) {
    return redirect()->route('blog', $request->query(), 301);
});
Route::get('/animations/{blogPost:slug}', function (Request $request, BlogPost $blogPost) {
    return redirect()->route('blog.show', ['blogPost' => $blogPost] + $request->query(), 301);
});

Route::get('/admin', [LoginController::class, 'adminEntry'])->name('admin.dashboard');
Route::post('/admin', [LoginController::class, 'login'])->middleware('guest')->name('login');

Route::get('/login', fn () => redirect('/', 302));

Route::middleware(['auth', 'administrator'])->group(function () {
    Route::get('/admin/wordpress-migration', [AdminWordPressMigrationController::class, 'index'])->name('admin.wordpress-migration.index');
    Route::post('/admin/wordpress-migration', [AdminWordPressMigrationController::class, 'store'])->name('admin.wordpress-migration.store');
    Route::post('/admin/wordpress-migration/workspace', [AdminWordPressMigrationController::class, 'storeWorkspace'])->name('admin.wordpress-migration.workspace.store');
    Route::post('/admin/wordpress-migration/{importId}/analyze', [AdminWordPressMigrationController::class, 'analyze'])->whereUuid('importId')->name('admin.wordpress-migration.analyze');
    Route::get('/admin/wordpress-migration/analysis', [AdminWordPressMigrationController::class, 'analysis'])->name('admin.wordpress-migration.analysis');
    Route::get('/admin/wordpress-migration/settings', [AdminWordPressMigrationController::class, 'settings'])->name('admin.wordpress-migration.settings');
    Route::put('/admin/wordpress-migration/settings', [AdminWordPressMigrationController::class, 'saveSettings'])->name('admin.wordpress-migration.settings.update');
    Route::get('/admin/wordpress-migration/seo', [AdminWordPressMigrationController::class, 'seo'])->name('admin.wordpress-migration.seo');
    Route::put('/admin/wordpress-migration/seo', [AdminWordPressMigrationController::class, 'saveSeo'])->name('admin.wordpress-migration.seo.update');
    Route::get('/admin/wordpress-migration/ready', [AdminWordPressMigrationController::class, 'ready'])->name('admin.wordpress-migration.ready');
    Route::post('/admin/wordpress-migration/{importId}/dry-run', [AdminWordPressMigrationController::class, 'dryRun'])->whereUuid('importId')->name('admin.wordpress-migration.dry-run');
    Route::get('/admin/wordpress-migration/{importId}/dry-run', [AdminWordPressMigrationController::class, 'showDryRun'])->whereUuid('importId')->name('admin.wordpress-migration.dry-run.show');
    Route::post('/admin/wordpress-migration/{importId}/confirm', [AdminWordPressMigrationController::class, 'confirm'])->whereUuid('importId')->name('admin.wordpress-migration.confirm');
    Route::get('/admin/wordpress-migration/import/{run}/progress', [AdminWordPressMigrationController::class, 'progress'])->name('admin.wordpress-migration.import.progress');
    Route::get('/admin/wordpress-migration/import/{run}/status', [AdminWordPressMigrationController::class, 'status'])->name('admin.wordpress-migration.import.status');
    Route::get('/admin/wordpress-migration/import/{run}/result', [AdminWordPressMigrationController::class, 'result'])->name('admin.wordpress-migration.import.result');
    Route::get('/admin/wordpress-migration/import/{run}/report', [AdminWordPressMigrationController::class, 'download'])->name('admin.wordpress-migration.import.download');
    Route::post('/admin/wordpress-migration/import/{run}/rollback', [AdminWordPressMigrationController::class, 'rollback'])->name('admin.wordpress-migration.import.rollback');
    Route::post('/admin/wordpress-migration/import/{run}/retry', [AdminWordPressMigrationController::class, 'retry'])->name('admin.wordpress-migration.import.retry');
    Route::delete('/admin/wordpress-migration/{importId}', [AdminWordPressMigrationController::class, 'destroy'])->whereUuid('importId')->name('admin.wordpress-migration.destroy');
    Route::get('/admin/statistics', [AdminStatisticsController::class, 'index'])->name('admin.statistics');

    Route::get('/admin/seo', [AdminSeoController::class, 'edit'])->name('admin.seo.edit');
    Route::put('/admin/seo', [AdminSeoController::class, 'update'])->name('admin.seo.update');

    Route::get('/admin/media-library', [AdminMediaController::class, 'index'])->name('admin.media-library');
    Route::post('/admin/media-library', [AdminMediaController::class, 'store'])->name('admin.media-library.store');
    Route::delete('/admin/media-library/{filename}', [AdminMediaController::class, 'destroy'])
        ->where('filename', '[A-Za-z0-9][A-Za-z0-9._-]*\.(?:jpe?g|png|webp|gif)')
        ->name('admin.media-library.destroy');

    Route::get('/admin/blog', [AdminBlogController::class, 'index'])->name('admin.blog');
    Route::get('/admin/blog/create', [AdminBlogController::class, 'showCreateForm'])->name('admin.blog.create');
    Route::post('/admin/blog/editor-images', [AdminBlogController::class, 'uploadEditorImage'])->name('admin.blog.editor-images.store');
    Route::post('/admin/blog', [AdminBlogController::class, 'store'])->name('admin.blog.store');
    Route::get('/admin/blog/{blogPost:id}/edit', [AdminBlogController::class, 'showEditForm'])->name('admin.blog.edit');
    Route::put('/admin/blog/{blogPost:id}', [AdminBlogController::class, 'update'])->name('admin.blog.update');
    Route::patch('/admin/blog/{blogPost:id}/position', [AdminBlogController::class, 'updatePosition'])->name('admin.blog.position.update');
    Route::delete('/admin/blog/{blogPost:id}/thumbnail', [AdminBlogController::class, 'destroyThumbnail'])->name('admin.blog.thumbnail.destroy');
    Route::delete('/admin/blog/{blogPost:id}', [AdminBlogController::class, 'destroy'])->name('admin.blog.destroy');

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

Route::fallback(function (Request $request) {
    if (! $request->isMethod('GET') || $request->is('admin/*') || ! Schema::hasTable('wordpress_import_redirects')) {
        abort(404);
    }
    $path = rtrim('/'.ltrim($request->path(), '/'), '/') ?: '/';
    $redirect = WordPressImportRedirect::query()->where('source_path', $path)->first();
    if (! $redirect || ! str_starts_with($redirect->target_path, '/blogas/') || $redirect->target_path === $path) {
        abort(404);
    }

    return redirect($redirect->target_path, 301);
})->name('wordpress-import.redirect');
