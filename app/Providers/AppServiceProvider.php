<?php

namespace App\Providers;

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
        if (app()->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \App\Models\PurchaseOrder::observe(\App\Observers\PurchaseOrderObserver::class);
        \App\Models\Production::observe(\App\Observers\ProductionObserver::class);
    }
}
