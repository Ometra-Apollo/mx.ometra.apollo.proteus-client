## Proteus Client para Laravel

Cliente PHP para consumir la API de **Proteus** dentro de aplicaciones **Laravel 10+**.

Expone una clase de servicio (`Proteus`), un **Service Provider** y una **facade**
para trabajar con media, metadatos, categorías y transformaciones.

### Instalación

- **Requerimientos**
  - **PHP**: ^8.0  
  - **Laravel**: ^11.0 o ^12.0

Instala el paquete vía Composer:

```bash
composer require ometra/proteus-client
```

Laravel detectará automáticamente el `ProteusServiceProvider` gracias a la
configuración en `composer.json`.

### Publicar configuración

Publica el archivo de configuración para poder definir la URL y el token:

```bash
php artisan vendor:publish --tag=proteus-config
```

Esto generará el archivo `config/proteus.php` en tu aplicación Laravel.

### Configuración

En tu archivo `.env` agrega:

```dotenv
PROTEUS_URL=https://tu-api-proteus.test
PROTEUS_TOKEN=tu-token-aqui
```

En `config/proteus.php` puedes definir:

- **url**: URL base de la API de Proteus.
- **token**: Token Bearer para autenticar las peticiones.
- **transformations**: Transformaciones disponibles.
- **formats**: Formatos de salida soportados.

### Uso básico

Puedes usar el cliente vía **inyección de dependencias** o vía **facade**.

#### Mediante Facade

```php
use Ometra\Apollo\Proteus\Facades\Proteus;

// Listar media
$media = Proteus::mediaIndex([
    'page' => 1,
    'per_page' => 20,
]);

// Ver detalle de un media
$item = Proteus::mediaShow('media-id');
```

#### Inyección de dependencias

```php
use Ometra\Apollo\Proteus\Proteus;

class MediaController
{
    public function index(Proteus $proteus)
    {
        $media = $proteus->mediaIndex(['page' => 1]);

        return view('media.index', compact('media'));
    }
}
```

### Subir archivos

El método `uploadFile` permite enviar archivos y metadatos a un endpoint de la API:

```php
use Ometra\Apollo\Proteus\Facades\Proteus;

$data = [
    'files' => [$request->file('file')], // UploadedFile[]
    'metadata' => [
        'title' => 'Mi archivo',
    ],
    'transformations' => [
        'thumbnail' => ['key' => 'thumb_preset'],
    ],
];

$response = Proteus::uploadFile('media/store', $data);
```

### Descarga de archivos

Para descargar un media como `StreamedResponse`:

```php
use Ometra\Apollo\Proteus\Facades\Proteus;

return Proteus::mediaDownload('media-id', 'mp4');
```

También puedes guardar el archivo directamente en el storage configurado
en Laravel:

```php
use Ometra\Apollo\Proteus\Facades\Proteus;

Proteus::saveMediaLocal('media-id', 'mi-archivo.mp4');
```

### Metadatos y categorías

- **Listar categorías**:

```php
$categories = Proteus::categoriesIndex();
```

- **Obtener definición de metadatos por clave**:

```php
$definition = Proteus::metadataKeys('genre');
```

- **Valores posibles para una clave de metadato**:

```php
$values = Proteus::metadataValuesFormKey('genre');
```

### Configuración avanzada

Puedes consultar la configuración de transformaciones y formatos
desde el propio cliente:

```php
use Ometra\Apollo\Proteus\Facades\Proteus;

$transformations = Proteus::transformationsConfig();
$formats = Proteus::formatsConfig();
```

### Licencia

Este paquete se distribuye bajo la licencia **MIT**.


