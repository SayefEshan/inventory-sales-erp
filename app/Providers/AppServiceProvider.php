<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // You can add any bootstrapping logic here if needed  // Set default string length for older MySQL versions
        Schema::defaultStringLength(191);

        // Use custom Tailwind pagination view
        Paginator::defaultView('components.pagination');
        Paginator::defaultSimpleView('components.pagination');

        // Prevent lazy loading in development
        Model::preventLazyLoading(!app()->isProduction());

        // Disable mass assignment protection for seeders only
        if (app()->runningInConsole()) {
            Model::unguard();
        }
    }
}
