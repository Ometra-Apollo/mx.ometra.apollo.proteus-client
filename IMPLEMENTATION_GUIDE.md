# Implementación de Proteus Context Middleware

## Configuración del Middleware

El paquete incluye el middleware `ProteusContextMiddleware` que automáticamente captura el `tenant_id` y `app_name` de cada request.

### 1. Para Laravel 11+ (bootstrap/app.php)

```php
use Ometra\Apollo\Proteus\Http\Middleware\ProteusContextMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'proteus.context' => ProteusContextMiddleware::class,
        ]);
    })
    // ... resto de configuración
    ->create();
```

### 2. Para Laravel 10 (app/Http/Kernel.php)

En el método `boot()` o en la propiedad `$routeMiddleware`:

```php
protected $routeMiddleware = [
    // ... otros middlewares
    'proteus.context' => \Ometra\Apollo\Proteus\Http\Middleware\ProteusContextMiddleware::class,
];
```

## Uso del Middleware

### Aplicar a grupo de rutas

```php
// routes/api.php
Route::middleware('proteus.context')->group(function () {
    Route::post('/media/upload', MediaController::class . '@upload');
    Route::get('/categories', CategoryController::class . '@index');
    // ... más rutas
});
```

### Enviar tenant_id y app_name

El middleware busca estos valores en **este orden**:

#### Opción 1: Headers HTTP (Recomendado)
```bash
curl -X POST http://localhost/api/media/upload \
  -H "X-Tenant-ID: 1" \
  -H "X-App-Name: mi_aplicacion"
```

#### Opción 2: Query Parameters
```bash
curl -X POST "http://localhost/api/media/upload?tenant_id=1&app_name=mi_aplicacion"
```

### Uso en Controladores

Una vez aplicado el middleware, instancia `Proteus` sin parámetros:

```php
<?php

namespace App\Http\Controllers;

use Ometra\Apollo\Proteus\Proteus;

class MediaController extends Controller
{
    public function upload(Proteus $proteus)
    {
        // El middleware resolvió automáticamente tenant_id y app_name
        $result = $proteus->uploadFile([
            'file' => request()->file('file'),
            'name' => request()->input('name')
        ]);

        return response()->json($result);
    }
}
```

### Inyección de Dependencias

También puedes usar inyección de dependencias:

```php
<?php

namespace App\Http\Controllers;

use Ometra\Apollo\Proteus\Proteus;

class CategoryController extends Controller
{
    public function __construct(
        private Proteus $proteus
    ) {}

    public function index()
    {
        return $this->proteus->categoriesIndex();
    }
}
```

## Alternativas sin Middleware

Si prefieres no usar el middleware, puedes:

### Parámetros directos al instanciar
```php
$proteus = new Proteus(tenantId: 1, appName: 'mi_app');
```

### Establecer contexto manualmente
```php
use Ometra\Apollo\Proteus\Services\ProteusContext;

ProteusContext::set(1, 'mi_app');
$proteus = new Proteus();
```

## Mejores Prácticas

1. **Usar headers HTTP** es más seguro que query parameters
2. **Aplicar el middleware globalmente** o a nivel de grupo de rutas
3. **Validar tenant_id y app_name** en el controlador si es necesario
4. El middleware **limpia automáticamente** el contexto después de la request

## Troubleshooting

### Error: "No se encontró aplicación Proteus"
- Verifica que el `tenant_id` y `app_name` son correctos
- Asegúrate de haber ejecutado las migraciones: `php artisan migrate`
- Comprueba que la aplicación existe: `php artisan tinker` → `ProteusApp::where('tenant_id', 1)->where('name', 'mi_app')->first()`

### Error: "Error al descifrar el token"
- La aplicación puede estar corrupta
- Intenta regenerar el hash: `$app->regenerateHash('nuevo_token')`

### El middleware no se ejecuta
- Verifica que está registrado correctamente en `bootstrap/app.php` o `Kernel.php`
- Asegúrate de aplicarlo a las rutas: `Route::middleware('proteus.context')`
- Comprueba que los headers se envían correctamente con el request
