<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Simple locale switcher using session value (set via /lang/{locale})
        if (session()->has('locale')) {
            app()->setLocale(session('locale'));
        }
    }
}
