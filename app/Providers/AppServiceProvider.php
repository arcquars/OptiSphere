<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\NumberToWords;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(NumberToWords::class, function ($app) {
            return new NumberToWords();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
