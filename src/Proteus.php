<?php

namespace Ometra\Apollo\Proteus;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use GuzzleHttp\Exception\RequestException;
use Ometra\Apollo\Proteus\Partials\PayloadFormatter;
use Ometra\Apollo\Proteus\Partials\MediaDownloader;

/**
 * Cliente principal para consumir la API de Proteus.
 *
 * Esta clase expone métodos de alto nivel para gestionar media,
 * categorías, metadatos y transformaciones a través de la API.
 */
class Proteus extends BaseApiService
{
    /**
     * Crea una nueva instancia del cliente de Proteus.
     *
     * @param string|null $format
     * @throws RuntimeException 
     */
    public function __construct(string|null $format = null, protected PayloadFormatter $formatter, protected MediaDownloader $downloader)
    {
        parent::__construct(
            Config::get('proteus.url'),
            Config::get('proteus.token'),
            $format
        );
    }

    /**
     * Obtiene un listado de media desde Proteus.
     * @param array $data 
     * @return array 
     * @throws Exception
     */
    public function mediaIndex(array $data): array
    {
        try {
            return $this->request(method: 'GET', endpoint: 'media', data: $data);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Obtiene el detalle de un media por su identificador.
     * @param string $id 
     * @return array 
     * @throws Exception
     */
    public function mediaShow(string $id): array
    {
        try {
            return $this->request(method: 'GET', endpoint: 'media' . '/' . $id);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Crea un nuevo registro de media.
     * @param array $data 
     * @return array 
     * @throws Exception
     */
    public function UploadFile(array $data): array
    {
        try {
            $multipartPayload = $this->formatter->prepareMultipart($data);
            return $this->request(method: 'POST', endpoint: 'media', data: $multipartPayload, format: 'multipart');
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Elimina un media por su identificador.
     * @param string $id 
     * @return array 
     * @throws Exception
     */
    public function mediaDelete(string $id): array
    {
        try {
            return $this->request(method: 'DELETE', endpoint: 'media' . '/' . $id);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function requestTransformations(string $id_media, array $data): array
    {
        try {
            return $this->request(method: 'POST', endpoint: 'media/transformations' . $id_media . '/request-transformations', data: $data);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Obtiene las claves de metadatos disponibles en Proteus.
     * @param string $key
     * @return array
     * @throws Exception
     */
    public function metadataKeys(string $key): array
    {
        try {
            return $this->request(method: 'GET', endpoint: 'media/metadata' . '/' . $key);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Obtiene los valores asociados a una clave de metadato.
     * @param string $key 
     * @return array 
     * @throws Exception
     */
    public function metadataValuesFormKey(string $key): array
    {
        try {
            return $this->request(method: 'GET', endpoint: 'media/metadata/values/' . $key);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Obtiene los metadatos asociados a un media por su identificador.
     * @param string $id 
     * @return array 
     * @throws Exception
     */
    public function metadataShow(string $id, string $key): array
    {
        try {
            return $this->request(method: 'GET', endpoint: 'media/' . $id . '/metadata/' . $key);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Crea nuevos metadatos para un media.
     * @param string $id   
     * @param array  $data 
     * @return array
     * @throws Exception
     */
    public function metadataStore(string $id, array $data): array
    {
        $payload = $this->formatter->prepareMultipart($data);
        return $this->request(method: 'POST', endpoint: 'media/' . $id .'/metadata', data: $payload, format: 'multipart');
    }

    /**
     * Actualiza los metadatos de un media.
     * @param string $id   
     * @param array  $data 
     * @return array
     * @throws Exception
     */
    public function metadataUpdate(string $id, array $data): array
    {
        $payload = $this->formatter->prepareMultipart($data);
        return $this->request(method: 'POST', endpoint: 'media/' . $id, data: $payload, format: 'multipart');
    }

    /**
     * Elimina un metadato específico de un media.
     * @param string $id  
     * @param string $key 
     * @return array 
     * @throws Exception
     */
    public function metadataDelete(string $id, string $key): array
    {
        try {
            return $this->request(method: 'DELETE', endpoint: 'media/' . $id . '/metadata/' . $key);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Obtiene el listado de categorías disponibles en Proteus.
     * @return array 
     * @throws Exception
     */
    public function categoriesIndex(): array
    {
        try {
            return $this->request(method: 'GET', endpoint: 'categories');
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Crea una nueva categoría.
     * @param array $data
     * @return array
     */
    public function categoryStore(array $data): array
    {
        try {
            return $this->request(method: 'POST', endpoint: 'categories', data: $data);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Actualiza una categoría existente.
     * @param string $id
     * @param array  $data
     * @return array
     */
    public function categoryUpdate(string $id, array $data): array
    {
        try {
            return $this->request(method: 'POST', endpoint: 'categories' . '/' . $id, data: $data);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Elimina una categoría por su identificador.
     * @param string $id 
     * @return array 
     * @throws Exception
     */
    public function categoryDelete(string $id): array
    {
        try {
            return $this->request(method: 'DELETE', endpoint: 'categories' . '/' . $id);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Obtiene el detalle de una categoría por su identificador.
     * @param string $id 
     */
    public function categoryShow(string $id): array
    {
        try {
            return $this->request(method: 'GET', endpoint: 'categories' . '/' . $id);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Obtiene el listado de los directorios
     * @return array 
     * @throws Exception
     */
    public function directoriesIndex(): array
    {
        try {
            return $this->request(method: 'GET', endpoint: 'directories');
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Crea un nuevo directorio en Proteus.
     * @param array $data 
     * @return array 
     * @throws Exception
     */
    public function directoryStore(array $data): array
    {
        try {
            return $this->request(method: 'POST', endpoint: 'directories', data: $data);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Obtiene el detalle de un directorio por su identificador.
     * @param string $id
     * @return array 
     * @throws Exception
     */
    public function directoryShow(string $id): array
    {
        try {
            return $this->request(method: 'GET', endpoint: 'directories' . '/' . $id);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Elimina un directorio por su identificador.
     * @param string $id 
     * @return array
     * @throws Exception
     */
    public function directoryDelete(string $id): array
    {
        try {
            return $this->request(method: 'DELETE', endpoint: 'directories' . '/' . $id);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Actualiza los datos de un directorio.
     * @param string $id Identificador del directorio.
     * @param array $data Datos a actualizar.
     */
    public function directoryUpdate(string $id, array $data): array
    {
        try {
            return $this->request(method: 'POST', endpoint: 'directories/' . $id, data: $data);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Descarga un media desde Proteuss
     * @param string      $id                
     * @param string|null $ext               
     * @param int         $maxRetries        
     * @param int         $retryDelaySeconds 
     * @return StreamedResponse|\AWS\CRT\HTTP\Response
     * @throws Exception
     */
    public function mediaDownload(
        string $id,
        ?string $ext = null,
        int $maxRetries = 3,
        int $retryDelaySeconds = 5
    ): StreamedResponse {
        return $this->downloader->download($id, $ext, $maxRetries, $retryDelaySeconds);
    }

    /**
     * Descarga un media y lo guarda en el sistema de ficheros configurado
     * en Laravel a través del facade `Storage`.
     *
     * @param string $id       
     * @param string $filename
     * @return void
     * @throws Exception
     */
    public function saveMediaLocal(string $id, string $ext): void
    {
        try {
            $response = $this->requestDownload(
                method: 'GET',
                endpoint: 'media/' . $id . '/download',
                data: [
                    'ext' => $ext
                ]
            );
            $stream = $response->getBody();
            Storage::putStream($id, $stream);
            if (is_resource($stream)) {
                $stream->close();
            }
        } catch (RequestException $e) {
            throw new Exception('Error al guardar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene la información de preset asociada a un media.
     * @param string $id 
     * @return mixed|null
     */
    public function presetIndex(string $directory_id): array
    {
        try {
            return $this->request(method: 'GET', endpoint: 'directories/' . $directory_id . '/presets');
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Crea un nuevo preset de transformaciones.
     * @param array $data
     * @return array
     */
    public function presetStore(array $data): array
    {
        try {
            return $this->request(method: 'POST', endpoint: 'presets', data: $data);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Elimina un preset de transformaciones.
     * @param string $directory_id
     * @param string $preset_id
     * @return array
     */
    public function presetDelete(string $directory_id, string $preset_id): array
    {
        try {
            return $this->request(method: 'DELETE', endpoint: 'directories/' . $directory_id . '/presets/' . $preset_id);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Obtiene el detalle de un preset de transformaciones.
     * @param string $directory_id
     * @param string $preset_id
     * @return array
     */
    public function presetShow(string $directory_id, string $preset_id): array
    {
        try {
            return $this->request(method: 'GET', endpoint: 'directories/' . $directory_id . '/presets/' . $preset_id);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Actualiza un preset de transformaciones.
     * @param string $directory_id
     * @param string $preset_id
     * @param array  $data
     * @return array
     */
    public function presetUpdate(string $directory_id, string $preset_id, array $data): array
    {
        try {
            return $this->request(method: 'POST', endpoint: 'directories/' . $directory_id . '/presets/' . $preset_id, data: $data);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Devuelve la configuración de transformaciones definida en `config/proteus.php`.
     * @return array
     */
    public function transformationsConfig(): array
    {
        return (array) Config::get('proteus.transformations', []);
    }

    /**
     * Devuelve la configuración de formatos definida en `config/proteus.php`.
     * @return array
     */
    public function formatsConfig(): array
    {
        return (array) Config::get('formats', []);
    }

    /**
     * Formatea un metadato usando el formateador interno.
     * @param string $key
     * @param mixed  $value
     * @return array
     */
    public function formatMetadata(string $key, mixed $value)
    {
        return $this->formatter->formatMetadata($key, $value);
    }
}
