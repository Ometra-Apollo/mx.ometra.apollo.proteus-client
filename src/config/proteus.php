<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Proteus API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the connection to Proteus API service
    |
    */

    'base_url' => env('PROTEUS_BASE_URL'),
    'context_class' => env('PROTEUS_CONTEXT_CLASS'),

     /**
      * Si se proporciona un tenant_id específico, se usará ese valor.
      * De lo contrario, se intentará obtener el tenant_id del contexto actual.
      * Si no se encuentra ningún tenant_id, se lanzará una excepción.
      */

    /**
     * Token de autenticación para consumir Proteus API
     * Generado con: php artisan keygen:generate "Flare" (en Proteus)
     */
    'app_token' => env('PROTEUS_APP_TOKEN', ''),

    /**
     * Timeout en segundos para requests HTTP
     */
    'timeout' => env('PROTEUS_TIMEOUT', 30),

    /**
     * Número de reintentos en caso de fallo
     */
    'retries' => env('PROTEUS_RETRIES', 3),

    /**
     * Delay en ms entre reintentos
     */
    'retry_delay' => env('PROTEUS_RETRY_DELAY', 100),

    // Legacy config (deprecated)
    'transformations' => [],
    'formats' => [],
];
