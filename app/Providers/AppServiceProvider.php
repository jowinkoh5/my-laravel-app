<?php

namespace App\Providers;

use Illuminate\Support\Number;
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
        // Set the number formatting defaults once, so no view has to name a
        // currency and none of them can drift apart.
        Number::useCurrency(config('app.currency'));
        Number::useLocale(config('app.currency_locale'));
    }
}
