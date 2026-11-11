<?php

namespace App\Providers;

use App\Support\Installer;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Before anything else: ensure an APP_KEY exists, and use file sessions/cache until the
        // database has been set up on first run.
        Installer::prepareRuntime();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
