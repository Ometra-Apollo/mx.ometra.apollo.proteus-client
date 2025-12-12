<?php

namespace Apollo\Proteus\Providers;

use Illuminate\Support\ServiceProvider;
use Apollo\Proteus\Proteus;

class ProteusServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Proteus::class, function ($app) {
            return new Proteus();
        });
    }

    public function boot()
    {
        $this->app->alias(Proteus::class, 'proteus');

        $this->publishes([
            __DIR__ . '/../config/proteus.php' => config_path('proteus.php'),
        ], 'proteus-config');
    }
}
