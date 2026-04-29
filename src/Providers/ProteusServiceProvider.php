<?php

namespace Ometra\Apollo\Proteus\Providers;

use Ometra\Apollo\Proteus\Proteus;
use Illuminate\Support\ServiceProvider;
use Ometra\Apollo\Proteus\Api\MediaApi;
use Ometra\Apollo\Proteus\Api\PresetsApi;
use Ometra\Apollo\Proteus\Api\MetadataApi;
use Ometra\Apollo\Proteus\Api\CategoriesApi;
use Ometra\Apollo\Proteus\Api\DirectoriesApi;

/**
 * Service Provider para registrar el cliente de Proteus en Laravel.
 *
 * Publica el archivo de configuración y registra los bindings principales
 * del cliente para ser inyectados o utilizados mediante la facade.
 */
class ProteusServiceProvider extends ServiceProvider
{
    private const CONFIG_PATH = __DIR__ . '/../../config/proteus.php';

    private const MIGRATIONS_PATH = __DIR__ . '/../../database/migrations';

    private const ROUTES_PATH = __DIR__ . '/../../routes/proteus.php';

    /**
     * Registra los bindings del cliente de Proteus como singleton.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(self::CONFIG_PATH, 'proteus');

        $this->app->singleton(MediaApi::class);
        $this->app->singleton(MetadataApi::class);
        $this->app->singleton(CategoriesApi::class);
        $this->app->singleton(DirectoriesApi::class);
        $this->app->singleton(PresetsApi::class);

        $this->app->singleton(Proteus::class, function ($app) {
            return new Proteus(
                $app->make(MediaApi::class),
                $app->make(MetadataApi::class),
                $app->make(CategoriesApi::class),
                $app->make(DirectoriesApi::class),
                $app->make(PresetsApi::class),
            );
        });

        $this->app->alias(Proteus::class, 'proteus');
    }

    /**
     * Inicializa el provider, publica la configuración y registra middleware.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(self::MIGRATIONS_PATH);
        $this->loadRoutesFrom(self::ROUTES_PATH);

        $this->publishes([
            self::CONFIG_PATH => config_path('proteus.php'),
        ], 'proteus-config');

        $this->publishes([
            self::MIGRATIONS_PATH => database_path('migrations'),
        ], 'proteus-migrations');
    }
}
