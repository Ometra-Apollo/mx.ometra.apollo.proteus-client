<?php

namespace Ometra\Apollo\Proteus\Providers;

use Ometra\Apollo\Proteus\Proteus;
use Illuminate\Support\ServiceProvider;

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
    public function register(): void
    {
        $this->app->singleton(Proteus::class, function ($app) {
            return new Proteus();
        });
        $this->app->alias(Proteus::class, 'proteus');
    }

    /**
     * Inicializa el provider, publica la configuración y registra alias.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/proteus.php' => config_path('proteus.php'),
        ], 'proteus-config');
    }
}
