<?php

namespace Ometra\Apollo\Proteus\Providers;

use Illuminate\Support\ServiceProvider;
use Ometra\Apollo\Proteus\Proteus;

/**
 * Service Provider para registrar el cliente de Proteus en Laravel.
 *
 * Publica el archivo de configuración y registra el binding principal
 * del cliente para ser inyectado o utilizado mediante la facade.
 */
class ProteusServiceProvider extends ServiceProvider
{
    /**
     * Registra el binding del cliente de Proteus como singleton.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Proteus::class, function ($app) {
            return new Proteus();
        });
    }

    /**
     * Inicializa el provider, publica la configuración y registra alias.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->alias(Proteus::class, 'proteus');

        $this->publishes([
            __DIR__ . '/../config/proteus.php' => config_path('proteus.php'),
        ], 'proteus-config');
    }
}
