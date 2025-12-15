<?php

namespace Ometra\Apollo\Proteus;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use GuzzleHttp\Exception\RequestException;

class Proteus extends BaseApiService
{
    public function __construct(string|null $format = null)
    {
        parent::__construct(
            Config::get('proteus.url'),
            Config::get('proteus.token'),
            $format
        );
    }


    public function mediaIndex(array $data)
    {
        try {
            return $this->request(method: 'GET', endpoint: 'media', data: $data);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function mediaShow(string $id)
    {
        try {
            return $this->request(method: 'GET', endpoint: 'media' . '/' . $id);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function mediaUpdate(string $id, array $data)
    {
        try {
            return $this->request(method: 'POST', endpoint: 'media' . '/' . $id . '/metadata', data: $data, format: 'multipart');
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function mediaStore(array $data)
    {
        try {
            return $this->request(method: 'POST', endpoint: 'media/store', data: $data);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function mediaDelete(string $id)
    {
        try {
            return $this->request(method: 'DELETE', endpoint: 'media' . '/' . $id);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

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

    public function categoriesIndex()
    {
        try {
            return $this->request(method: 'GET', endpoint: 'categories');
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

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

    private function processFiles(string $key, array $files): array
    {
        return array_map(fn($file) => $this->formatFile($key, $file), $files);
    }

    private function formatTransformations(string $key, mixed $value): array
    {
        return [
            'name'     => "transformations[$key]",
            'contents' => $value['key'],
        ];
    }

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

    private function formatField(string $key, mixed $value): array
    {
        return [
            'name'     => $key,
            'contents' => is_array($value) ? json_encode($value) : $value,
        ];
    }

    public function formatMetadata(string $key, mixed $value): array
    {
        return [
            'name'     => "metadata[$key]",
            'contents' => is_array($value) ? json_encode($value) : $value,
        ];
    }

    public function metadataKeys(string $key)
    {
        try {
            return $this->request(method: 'GET', endpoint: 'media/metadata' . '/' . $key);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function metadataValuesFormKey(string $key)
    {
        try {
            return $this->request(method: 'GET', endpoint: 'media/metadata/' . $key . '/values');
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function transformationsConfig(): array
    {
        return (array) Config::get('proteus.transformations', []);
    }

    public function formatsConfig(): array
    {
        return (array) Config::get('formats', []);
    }

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
