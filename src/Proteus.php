<?php

namespace Ometra\Apollo\Proteus;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use GuzzleHttp\Exception\RequestException;

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
     * @param string|null $format Formato del contenido de la petición.
     *                            Null => JSON, cualquier otro => audio/mpeg.
     *
     * @throws \RuntimeException Si la URL o el token no están configurados.
     */
    public function __construct(string|null $format = null)
    {
        parent::__construct(
            Config::get('proteus.url'),
            Config::get('proteus.token'),
            $format
        );
    }

    /**
     * Obtiene un listado de media desde Proteus.
     *
     * @param array $data Parámetros de filtrado/paginación.
     *
     * @return array Respuesta de la API.
     *
     * @throws Exception
     */
    public function mediaIndex(array $data)
    {
        try {
            return $this->request(method: 'GET', endpoint: 'media', data: $data);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Obtiene el detalle de un media por su identificador.
     *
     * @param string $id Identificador del media.
     *
     * @return array Respuesta de la API.
     *
     * @throws Exception
     */
    public function mediaShow(string $id)
    {
        try {
            return $this->request(method: 'GET', endpoint: 'media' . '/' . $id);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Actualiza los metadatos de un media.
     *
     * @param string $id   Identificador del media.
     * @param array  $data Datos de metadatos a actualizar.
     *
     * @return array Respuesta de la API.
     *
     * @throws Exception
     */
    public function mediaUpdate(string $id, array $data)
    {
        try {
            return $this->request(method: 'POST', endpoint: 'media' . '/' . $id . '/metadata', data: $data, format: 'multipart');
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Crea un nuevo registro de media.
     *
     * @param array $data Datos del media a crear.
     *
     * @return array Respuesta de la API.
     *
     * @throws Exception
     */
    public function mediaStore(array $data)
    {
        try {
            return $this->request(method: 'POST', endpoint: 'media/store', data: $data);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Elimina un media por su identificador.
     *
     * @param string $id Identificador del media.
     *
     * @return array Respuesta de la API.
     *
     * @throws Exception
     */
    public function mediaDelete(string $id)
    {
        try {
            return $this->request(method: 'DELETE', endpoint: 'media' . '/' . $id);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Descarga un media desde Proteus.
     *
     * Puede reintentar la descarga cuando la API aún está procesando
     * el archivo (HTTP 202) hasta un máximo de intentos.
     *
     * @param string      $id                Identificador del media.
     * @param string|null $ext               Extensión o formato solicitado.
     * @param int         $maxRetries        Máximo de reintentos cuando la
     *                                       respuesta es 202.
     * @param int         $retryDelaySeconds Tiempo de espera entre reintentos
     *                                       en segundos.
     *
     * @return StreamedResponse|\AWS\CRT\HTTP\Response
     *
     * @throws Exception
     */
    public function mediaDownload(
        string $id,
        string|null $ext,
        int $maxRetries = 3,
        int $retryDelaySeconds = 5
    ): StreamedResponse|\AWS\CRT\HTTP\Response {

        $attempt = 0;
        do {
            try {
                $extension = $ext ?? '';

                $response = $this->requestDownload(
                    method: 'GET',
                    endpoint: 'media' . '/' . $id . '/download?ext=' . $extension,
                    format: 'stream'
                );

                $statusCode = $response->getStatusCode();

                if ($statusCode == 200) {

                    $stream = $response->getBody();
                    $contentType = $response->getHeaderLine('Content-Type') ?: 'application/octet-stream';
                    $contentLength = $response->getHeaderLine('Content-Length');
                    $disposition = 'attachment; filename="' . $id . '"';

                    $streamedResponse = new StreamedResponse(function () use ($stream) {
                        while (!$stream->eof()) {
                            echo $stream->read(8192);
                            flush();
                        }
                    });

                    $streamedResponse->headers->set('Content-Type', $contentType);
                    $streamedResponse->headers->set('Content-Disposition', $disposition);

                    if ($contentLength) {
                        $streamedResponse->headers->set('Content-Length', $contentLength);
                    }

                    $streamedResponse->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');

                    return $streamedResponse;
                }

                $content = $response->getBody()->getContents();
                $body = json_decode($content, true);
                $message = $body['message'] ?? 'Error inesperado';

                if ($statusCode === 202) {
                    if (++$attempt > $maxRetries) {
                        throw new Exception("Máximo reintentos alcanzado. Mensaje: {$message}");
                    }

                    sleep($retryDelaySeconds);
                    continue;
                }

                throw new Exception("Error inesperado en descarga: código HTTP {$statusCode}. Mensaje: {$message}");
            } catch (RequestException $e) {
                throw new Exception($e->getMessage());
            }
        } while ($attempt <= $maxRetries);

        throw new Exception("No se pudo descargar el archivo {$id} después de {$maxRetries} intentos");
    }

    /**
     * Descarga un media y lo guarda en el sistema de ficheros configurado
     * en Laravel a través del facade `Storage`.
     *
     * @param string $id       Identificador del media.
     * @param string $filename Nombre del archivo a guardar (no se usa
     *                         actualmente en la clave de almacenamiento).
     *
     * @return void
     *
     * @throws Exception
     */
    public function saveMediaLocal(string $id, string $filename): void
    {
        try {

            $response = $this->requestDownload(
                method: 'GET',
                endpoint: 'media/' . $id . '/download',
                format: 'stream'
            );

            $stream = $response->getBody();

            Storage::putStream($id, $stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        } catch (RequestException $e) {
            throw new Exception('Error al guardar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene el listado de categorías disponibles en Proteus.
     *
     * @return array Respuesta de la API.
     *
     * @throws Exception
     */
    public function categoriesIndex()
    {
        try {
            return $this->request(method: 'GET', endpoint: 'categories');
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Sube uno o varios archivos a un endpoint determinado de Proteus.
     *
     * Este método genera el arreglo `multipart` esperado por Guzzle
     * para envíos de ficheros y metadatos.
     *
     * @param string $endpoint Endpoint relativo de la API donde enviar los archivos.
     * @param array  $data     Datos del formulario, incluyendo instancias de
     *                         `UploadedFile`, metadatos y transformaciones.
     *
     * @return array Respuesta de la API.
     *
     * @throws Exception
     */
    public function uploadFile(string $endpoint, array $data)
    {
        try {
            $multipart = [];
            foreach ($data as $key => $value) {
                if ($key == "transformations") {
                    foreach ($value as $t_value_key => $t) {
                        $multipart[] = $this->formatTransformations($t_value_key, $t);
                    }
                } elseif (is_array($value)) {
                    foreach ($value as $value_key => $file) {
                        if ($file instanceof UploadedFile) {
                            $multipart = array_merge($multipart, $this->processFiles($key, $value));
                        } else {
                            $multipart[] = $this->formatMetadata($value_key, $file);
                        }
                    }
                } elseif ($value instanceof UploadedFile) {
                    $multipart[] = $this->formatFile($key, $value);
                } else {
                    $multipart[] = $this->formatField($key, $value);
                }
            }

            return $this->request(method: 'POST', endpoint: $endpoint, data: $multipart, format: 'multipart');
        } catch (Exception $e) {
            throw new Exception("Error al subir archivo: " . $e->getMessage());
        }
    }

    /**
     * Envía metadatos a un endpoint determinado usando formato multipart.
     *
     * @param string $endpoint Endpoint relativo de la API.
     * @param array  $data     Arreglo asociativo de metadatos.
     *
     * @return array Respuesta de la API.
     *
     * @throws Exception
     */
    public function setMetadata(string $endpoint, array $data)
    {
        try {
            $multipart = [];

            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $value_key => $file) {
                        $multipart[] = $this->formatMetadata($value_key, $file);
                    }
                } else {
                    $multipart[] = $this->formatField($key, $value);
                }
            }

            return $this->request(method: 'POST', endpoint: $endpoint, data: $multipart, format: 'multipart');
        } catch (Exception $e) {
            throw new Exception("Error al realizar transformaciones: " . $e->getMessage());
        }
    }

    /**
     * Procesa un arreglo de archivos y los transforma en partes multipart.
     *
     * @param string $key   Nombre del campo.
     * @param array  $files Arreglo de instancias de `UploadedFile`.
     *
     * @return array
     */
    private function processFiles(string $key, array $files): array
    {
        return array_map(fn($file) => $this->formatFile($key, $file), $files);
    }

    /**
     * Formatea un valor de transformación para el envío multipart.
     *
     * @param string $key   Clave de la transformación.
     * @param mixed  $value Valor o configuración de la transformación.
     *
     * @return array
     */
    private function formatTransformations(string $key, mixed $value): array
    {
        return [
            'name'     => "transformations[$key]",
            'contents' => $value['key'],
        ];
    }

    /**
     * Formatea un archivo individual para el envío multipart.
     *
     * @param string       $key  Nombre del campo.
     * @param UploadedFile $file Archivo subido desde Laravel.
     *
     * @return array
     *
     * @throws Exception Si el archivo no existe en el sistema de archivos.
     */
    private function formatFile(string $key, UploadedFile $file): array
    {
        if (!file_exists($file->getPathname())) {
            throw new Exception("El archivo no existe en la ruta: " . $file->getPathname());
        }

        return [
            'name'     => $key . '[]',
            'contents' => fopen($file->getRealPath(), 'r'),
            'filename' => $file->getClientOriginalName(),
        ];
    }

    /**
     * Formatea un campo simple (no archivo) para el envío multipart.
     *
     * @param string $key   Nombre del campo.
     * @param mixed  $value Valor del campo.
     *
     * @return array
     */
    private function formatField(string $key, mixed $value): array
    {
        return [
            'name'     => $key,
            'contents' => is_array($value) ? json_encode($value) : $value,
        ];
    }

    /**
     * Formatea un campo de metadatos para el envío multipart.
     *
     * @param string $key   Clave del metadato.
     * @param mixed  $value Valor del metadato.
     *
     * @return array
     */
    public function formatMetadata(string $key, mixed $value): array
    {
        return [
            'name'     => "metadata[$key]",
            'contents' => is_array($value) ? json_encode($value) : $value,
        ];
    }

    /**
     * Obtiene los metadatos configurados para una clave dada.
     *
     * @param string $key Clave del metadato.
     *
     * @return array Respuesta de la API.
     *
     * @throws Exception
     */
    public function metadataKeys(string $key)
    {
        try {
            return $this->request(method: 'GET', endpoint: 'media/metadata' . '/' . $key);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Obtiene los valores posibles para una clave de metadato.
     *
     * @param string $key Clave del metadato.
     *
     * @return array Respuesta de la API.
     *
     * @throws Exception
     */
    public function metadataValuesFormKey(string $key)
    {
        try {
            return $this->request(method: 'GET', endpoint: 'media/metadata/' . $key . '/values');
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Devuelve la configuración de transformaciones definida en `config/proteus.php`.
     *
     * @return array
     */
    public function transformationsConfig(): array
    {
        return (array) Config::get('proteus.transformations', []);
    }

    /**
     * Devuelve la configuración de formatos definida en `config/proteus.php`.
     *
     * @return array
     */
    public function formatsConfig(): array
    {
        return (array) Config::get('formats', []);
    }

    /**
     * Obtiene la información de preset asociada a un media.
     *
     * Si ocurre un error la función retorna null.
     *
     * @param string $id Identificador del media.
     *
     * @return mixed|null
     */
    public function presetByMedia(string $id): mixed
    {
        try {
            return $this->request(method: 'GET', endpoint: 'media/' . $id . '/preset');
        } catch (RequestException $e) {
            return null;
        } catch (Exception $e) {
            return null;
        }
    }
}
