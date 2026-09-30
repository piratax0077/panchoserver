<?php

namespace App\Providers;

use App\Observers\RepuestoStockObserver;
use App\repuesto;
use Illuminate\Support\ServiceProvider;
use Debugbar;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        repuesto::observe(RepuestoStockObserver::class);
    }
}
