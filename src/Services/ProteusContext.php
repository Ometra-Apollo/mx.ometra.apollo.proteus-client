<?php

namespace Ometra\Apollo\Proteus\Services;

/**
 * Servicio para gestionar el contexto de Proteus.
 * 
 * Almacena el tenant_id y app_name de forma global durante la request.
 */
class ProteusContext
{
    /**
     * El tenant_id actual.
     *
     * @var int|null
     */
    private static ?int $tenantId = null;

    /**
     * El app_name actual.
     *
     * @var string|null
     */
    private static ?string $appName = null;

    /**
     * Establece el tenant_id y app_name.
     *
     * @param int $tenantId
     * @param string $appName
     * @return void
     */
    public static function set(int $tenantId, string $appName): void
    {
        self::$tenantId = $tenantId;
        self::$appName = $appName;
    }

    /**
     * Obtiene el tenant_id actual.
     *
     * @return int|null
     */
    public static function getTenantId(): ?int
    {
        return self::$tenantId;
    }

    /**
     * Obtiene el app_name actual.
     *
     * @return string|null
     */
    public static function getAppName(): ?string
    {
        return self::$appName;
    }

    /**
     * Verifica si hay contexto establecido.
     *
     * @return bool
     */
    public static function isSet(): bool
    {
        return self::$tenantId !== null && self::$appName !== null;
    }

    /**
     * Limpia el contexto.
     *
     * @return void
     */
    public static function clear(): void
    {
        self::$tenantId = null;
        self::$appName = null;
    }
}
