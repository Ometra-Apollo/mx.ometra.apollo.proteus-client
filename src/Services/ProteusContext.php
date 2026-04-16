<?php

namespace Ometra\Apollo\Proteus\Services;

/**
 * Servicio para gestionar el contexto de Proteus.
 * 
 * Almacena el tenant_id y app_name de forma global durante la request.
 * El tenant es implícito; use withoutTenant() solo si necesita excluirlo.
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
     * Flag para excluir explícitamente el tenant.
     *
     * @var bool
     */
    private static bool $excludeTenant = false;

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
        self::$excludeTenant = false;
    }

    /**
     * Obtiene el tenant_id actual.
     * 
     * Retorna null si fue excluído explícitamente.
     *
     * @return int|null
     */
    public static function getTenantId(): ?int
    {
        return self::$excludeTenant ? null : self::$tenantId;
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
     * Excluye el tenant para la siguiente operación.
     * 
     * Úsalo solo cuando necesites explícitamente ignorar el tenant.
     *
     * @return void
     */
    public static function withoutTenant(): void
    {
        self::$excludeTenant = true;
    }

    /**
     * Restaura el tenant (incluye flag).
     *
     * @return void
     */
    public static function withTenant(): void
    {
        self::$excludeTenant = false;
    }

    /**
     * Limpia el contexto completamente.
     *
     * @return void
     */
    public static function clear(): void
    {
        self::$tenantId = null;
        self::$appName = null;
        self::$excludeTenant = false;
    }
}
