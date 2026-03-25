# Proteus Apps - Guía de Implementación

## Resumen
Se han agregado los siguientes componentes para gestionar aplicaciones Proteus con tokens descifrados:

### 1. **Migración de Base de Datos**
**Archivo:** `src/Database/Migrations/2026_03_25_000000_create_proteus_apps_table.php`

Crea la tabla `proteus_apps` con los siguientes campos:
- `id`: Identificador único (AUTO INCREMENT)
- `tenant_id`: ID del tenant (indexado)
- `name`: Nombre único de la aplicación
- `hash`: Token cifrado con bcrypt
- `timestamps`: Fechas de creación y actualización
- `softDeletes`: Eliminación lógica

### 2. **Modelo Eloquent**
**Archivo:** `src/Models/ProteusApp.php`

Proporciona los métodos CRUD completos:

#### Métodos de Lectura
- `find($id)`: Obtiene una aplicación por ID (heredado)
- `findByHash($hash)`: Obtiene una aplicación por su hash cifrado
- `getByTenant($tenantId)`: Obtiene todas las aplicaciones de un tenant

#### Métodos de Creación
- `create(array $data)`: Crea una aplicación (heredado)
- `createApp(array $data)`: Crea una aplicación cifrado el token automáticamente

#### Métodos de Actualización
- `update(array $data)`: Actualiza atributos (heredado)
- `updateApp(array $data)`: Actualiza una aplicación
- `regenerateHash(string $newToken)`: Regenera el hash con un nuevo token

#### Métodos de Eliminación
- `delete()`: Eliminación lógica (heredado, soft delete)
- `deleteApp()`: Alias para eliminar
- `restore()`: Restaura un registro eliminado (heredado)
- `restoreApp()`: Alias para restaurar

#### Métodos Utilitarios
- `encryptToken(string $token)`: Cifra un token con bcrypt
- `verifyToken(string $token, string $hash)`: Verifica si un token es válido
- `isValidToken(string $token)`: Verifica un token para esta aplicación
- `tenant()`: Relación con el modelo Tenant

### 3. **Comando Artisan**
**Archivo:** `src/Commands/StoreProteusAppCommand.php`

Comando para crear aplicaciones desde la línea de comandos, con token descifrado.

#### Uso
```bash
# Con argumentos
php artisan proteus:app:store --tenant_id=1 --name="Mi App" --token="mi_token_secreto"

# Modo interactivo
php artisan proteus:app:store
```

#### Características
- Solicita `tenant_id`, `name` y `token` descifrado
- Valida todos los datos de entrada
- Prevención de duplicados
- Cifra automáticamente el token antes de almacenar
- Salida formateada en tabla
- Manejo de errores

### 4. **Registro del Service Provider**
**Archivo:** `src/Providers/ProteusServiceProvider.php`

Se ha actualizado para:
- Publicar las migraciones
- Registrar el comando Artisan

## Instalación y Uso

### 1. Publicar Migraciones
```bash
php artisan vendor:publish --tag=proteus-migrations
```

### 2. Ejecutar Migraciones
```bash
php artisan migrate
```

### 3. Crear una Aplicación

**Opción A: Mediante comando**
```bash
php artisan proteus:app:store --tenant_id=1 --name="Mi Aplicación" --token="token_secreto_123"
```

**Opción B: En código PHP**
```php
use Ometra\Apollo\Proteus\Models\ProteusApp;

// Crear aplicación con token que será cifrado automáticamente
$app = ProteusApp::createApp([
    'tenant_id' => 1,
    'name' => 'Mi Aplicación',
    'token' => 'token_secreto_123'  // Se cifrará automáticamente
]);

// El token se guarda cifrado en $app->hash
echo $app->hash; // Hash cifrado con bcrypt
```

### 4. Operaciones CRUD

**Crear:**
```php
$app = ProteusApp::createApp([
    'tenant_id' => 1,
    'name' => 'Nueva App',
    'token' => 'token_secreto'
]);
```

**Leer:**
```php
$app = ProteusApp::find(1);
$appByHash = ProteusApp::findByHash('$2y$10$...');  // Hash cifrado
$appsByTenant = ProteusApp::getByTenant(1);
```

**Actualizar:**
```php
$app->updateApp(['name' => 'Nombre Nuevo']);
// O
$app->update(['name' => 'Nombre Nuevo']);
```

**Eliminar (Soft Delete):**
```php
$app->deleteApp();
// O
$app->delete();
```

**Restaurar:**
```php
$app->restoreApp();
// O
$app->restore();
```

**Regenerar Hash con nuevo Token:**
```php
$app->regenerateHash('nuevo_token_secreto');
```

### 5. Validación de Tokens

**Verificar si un token es válido:**
```php
if ($app->isValidToken('token_secreto')) {
    echo "Token válido";
} else {
    echo "Token inválido";
}
```

**Verificar de forma estática:**
```php
if (ProteusApp::verifyToken('token_secreto', '$2y$10$...')) {
    echo "Token válido";
}
```

## Configuración

Para utilizar un modelo de Tenant diferente, agrega en `config/proteus.php`:

```php
'tenant_model' => 'App\Models\Tenant',
```

## Relaciones

El modelo incluye una relación con el modelo Tenant:

```php
$tenant = $app->tenant;
```

## Características de Seguridad

- **Cifrado Bcrypt**: Los tokens se cifran usando `password_hash()` con algoritmo BCRYPT
- **Hash Oculto**: El campo `hash` se oculta en la serialización JSON
- **Soft Delete**: Los registros se eliminan lógicamente para mantener integridad de datos
- **Verificación**: Use `isValidToken()` o `verifyToken()` para verificar tokens
- **Nombre Único**: El nombre de la aplicación es único en la tabla

## Notas

- El token descifrado se proporciona desde el comando
- Se cifra automáticamente y se almacena como hash en el campo `hash`
- El token nunca se almacena en texto plano
- Use `isValidToken()` para verificar si un token es válido para una aplicación

