<?php

use App\Http\Controllers\LicensePushController;
use App\Http\Middleware\RequireLicense;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// A fresh copy may come without these folders; Laravel cannot start without them.
foreach (['storage/app/.sysconf', 'storage/framework/.license', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs', 'bootstrap/cache'] as $dir) {
    if (! is_dir(dirname(__DIR__).'/'.$dir)) {
        @mkdir(dirname(__DIR__).'/'.$dir, 0755, true);
    }
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        // Called by the license panel; no session or CSRF (works even before the database is set up).
        then: fn () => Route::post('license/push', LicensePushController::class)->middleware('throttle:30,1')->name('license.push'),
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global, before routing: the whole site is closed until licensed + installed (no route to find).
        $middleware->prepend(RequireLicense::class);
        // Signed-in admins who open /admin/login go to the dashboard.
        $middleware->redirectUsersTo('/admin');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
