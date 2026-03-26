<?php

namespace Ometra\Apollo\Proteus\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Ometra\Apollo\Proteus\Services\ProteusContext;

/**
 * Middleware para establecer el contexto de Proteus.
 *
 * Este middleware obtiene el tenant_id de forma independiente desde:
 * 1. Header X-Tenant-ID
 * 2. Query parameter tenant_id
 * 3. Request attribute tenant_id (establecido por otros middlewares)
 * 4. Resolver configurado en config('bee-hive.resolver') si está disponible
 *
 * Uso en routes:
 * Route::middleware('proteus.context')->group(function () {
 *     // rutas
 * });
 */
class ProteusContextMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Obtener tenant_id de múltiples fuentes (en orden de preferencia)
        $tenantId = $request->header('X-Tenant-ID') 
            ?? $request->query('tenant_id')
            ?? $request->attributes->get('tenant_id');
        
        // Si aún no hay tenant_id, intentar obtener del resolver configurado
        if (!$tenantId) {
            $resolverClass = config('bee-hive.resolver');
            if ($resolverClass && class_exists($resolverClass)) {
                try {
                    $tenantId = app($resolverClass)->resolveTenantId();
                } catch (\Exception $e) {
                    // Si falla, continuar sin tenant_id
                }
            }
        }
        
        // Primero intenta PROTEUS_APP_NAME, si no existe usa APP_NAME, sino usa default_app
        $appName = ucfirst(strtolower(env('PROTEUS_APP_NAME') ?? env('APP_NAME') ?? 'default_app'));
        
        // Si se proporcionan tenant_id y app_name, establecer el contexto
        if ($tenantId && $appName) {
            ProteusContext::set(
                (int) $tenantId,
                $appName
            );
        }

        $response = $next($request);

        // Limpiar el contexto después de la request
        ProteusContext::clear();

        return $response;
    }
}
