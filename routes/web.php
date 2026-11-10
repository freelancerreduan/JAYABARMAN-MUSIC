<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

// ---------- Website ----------
Route::controller(SiteController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/music', 'music')->name('music');
    Route::get('/visual-art', 'visualArt')->name('visual-art');
    Route::get('/theatre', 'theatre')->name('theatre');
    Route::get('/portfolio', 'portfolio')->name('portfolio');
    Route::get('/about-me', 'about')->name('about');
    Route::get('/contacts', 'contacts')->name('contacts');
});

// Old static .html links keep working
foreach (['index' => '/', 'music' => '/music', 'visual-art' => '/visual-art', 'theatre' => '/theatre',
    'portfolio' => '/portfolio', 'about-me' => '/about-me', 'contacts' => '/contacts'] as $file => $to) {
    Route::permanentRedirect("/{$file}.html", $to);
}

// ---------- Admin ----------
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/admin/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
});

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::post('sort', Admin\SortController::class)->name('sort');

    // Home, Contacts, Settings
    Route::controller(Admin\PageController::class)->group(function () {
        Route::get('home', 'home')->name('home');
        Route::put('home', 'saveHome');
        Route::get('contacts', 'contacts')->name('contacts');
        Route::put('contacts', 'saveContacts');
        Route::get('settings', 'settings')->name('settings');
        Route::put('settings', 'saveSettings');
        Route::put('settings/account', 'saveAccount')->name('account');
    });

    // Music: albums, songs, videos
    Route::controller(Admin\MusicController::class)->group(function () {
        Route::get('music', 'index')->name('music');
        Route::post('albums', 'storeAlbum')->name('albums.store');
        Route::put('albums/{album}', 'updateAlbum')->name('albums.update');
        Route::delete('albums/{album}', 'destroyAlbum')->name('albums.destroy');
        Route::post('albums/{album}/tracks', 'storeTracks')->name('tracks.store');
        Route::put('tracks/{track}', 'updateTrack')->name('tracks.update');
        Route::delete('tracks/{track}', 'destroyTrack')->name('tracks.destroy');
        Route::get('videos/create', 'createVideo')->name('videos.create');
        Route::post('videos', 'storeVideo')->name('videos.store');
        Route::get('videos/{video}/edit', 'editVideo')->name('videos.edit');
        Route::put('videos/{video}', 'updateVideo')->name('videos.update');
        Route::delete('videos/{video}', 'destroyVideo')->name('videos.destroy');
    });

    // Visual Art and Theatre photos
    Route::get('gallery/{section}', [Admin\GalleryController::class, 'index'])->whereIn('section', ['visual-art', 'theatre'])->name('gallery');
    Route::post('gallery/{section}', [Admin\GalleryController::class, 'store'])->whereIn('section', ['visual-art', 'theatre'])->name('gallery.store');
    Route::delete('gallery-images/{image}', [Admin\GalleryController::class, 'destroy'])->name('gallery.destroy');

    // Portfolio groups
    Route::controller(Admin\PortfolioController::class)->group(function () {
        Route::get('portfolio', 'index')->name('portfolio');
        Route::post('portfolio', 'store')->name('portfolio.store');
        Route::put('portfolio/{group}', 'update')->name('portfolio.update');
        Route::delete('portfolio/{group}', 'destroy')->name('portfolio.destroy');
        Route::delete('portfolio-images/{image}', 'destroyImage')->name('portfolio.image.destroy');
    });

    // About Me
    Route::controller(Admin\AboutController::class)->group(function () {
        Route::get('about', 'index')->name('about');
        Route::put('about/photo', 'savePhoto')->name('about.photo');
        Route::put('about/details', 'saveDetails')->name('about.details');
        Route::get('about/blocks/create', 'create')->name('about.create');
        Route::post('about/blocks', 'store')->name('about.store');
        Route::get('about/blocks/{block}/edit', 'edit')->name('about.edit');
        Route::put('about/blocks/{block}', 'update')->name('about.update');
        Route::delete('about/blocks/{block}', 'destroy')->name('about.destroy');
    });
});
