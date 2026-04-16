<?php

namespace Ometra\Apollo\Proteus\Providers;

use Ometra\Apollo\Proteus\Proteus;
use Ometra\Apollo\Proteus\Commands\StoreProteusAppCommand;
use Ometra\Apollo\Proteus\Services\ProteusClient;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider para registrar el cliente de Proteus en Laravel.
 *
 * Publica el archivo de configuración y registra los bindings principales
 * del cliente para ser inyectados o utilizados mediante la facade.
 */
class ProteusServiceProvider extends ServiceProvider
{
    /**
     * Registra los bindings del cliente de Proteus como singleton.
     *
     * @return void
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(__DIR__ . '/../config/proteus.php', 'proteus');

        // Legacy Proteus class
        $this->app->singleton(Proteus::class, function ($app) {
            return new Proteus();
        });
        $this->app->alias(Proteus::class, 'proteus');

        // New HTTP client
        $this->app->singleton(ProteusClient::class);
    }

    /**
     * Inicializa el provider, publica la configuración y registra middleware.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/proteus.php' => config_path('proteus.php'),
        ], 'proteus-config');

        // Publicar las migraciones
        $this->publishes([
            __DIR__ . '/../Database/Migrations' => database_path('migrations'),
        ], 'proteus-migrations');

        // Registrar comando
        if ($this->app->runningInConsole()) {
            $this->commands([
                StoreProteusAppCommand::class,
            ]);
        }

        // Registrar alias del middleware
        if (method_exists($this->app, 'routingMiddleware')) {
            $this->app->routingMiddleware('proteus.context', ProteusContextMiddleware::class);
        }
    }
}
