<?php

namespace Ometra\Apollo\Proteus\Partials;

use Exception;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait DownloadMedia
{
    /**
     * Ejecuta la lógica de descarga con reintentos y streaming.
     * @param string $id
     * @param string|null $ext
     * @param int $maxRetries
     * @param int $retryDelaySeconds
     */
    public function download(
        string $id,
        ?string $ext,
        int $maxRetries = 3,
        int $retryDelaySeconds = 5
    ): StreamedResponse {
        $attempt = 0;
        $extension = $ext ?? '';

        do {
            try {
                $response = $this->requestDownload(
                    method: 'GET',
                    endpoint: "media/{$id}/download?ext={$extension}",
                    format: 'stream'
                );

                $statusCode = $response->getStatusCode();

                if ($statusCode === 200) {
                    return $this->createStreamedResponse($extension, $id, $response);
                }

                if ($statusCode === 202) {
                    if (++$attempt > $maxRetries) {
                        throw new Exception("Máximo reintentos alcanzado (202 Processing).");
                    }
                    sleep($retryDelaySeconds);
                    continue;
                }

                $this->handleErrorResponse($response, $statusCode);
            } catch (RequestException $e) {
                throw new Exception("Error de red en descarga: " . $e->getMessage());
            }
        } while ($attempt <= $maxRetries);

        throw new Exception("No se pudo completar la descarga tras {$maxRetries} intentos.");
    }

    /**
     * Convierte la respuesta de Guzzle en una StreamedResponse de Symfony/Laravel.
     * @param string $id
     * @param mixed  $response
     */
    protected function createStreamedResponse(string $ext, string $id, $response): StreamedResponse
    {
        $stream = $response->getBody();
        $contentType = $response->getHeaderLine('Content-Type') ?: 'application/octet-stream';
        $contentLength = $response->getHeaderLine('Content-Length');
        $filename = $ext ? "{$id}.{$ext}" : $id;

        $streamedResponse = new StreamedResponse(function () use ($stream) {
            while (!$stream->eof()) {
                echo $stream->read(8192);
                flush();
            }
        });

        $streamedResponse->headers->add([
            'Content-Type'        => $contentType,
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ]);

        if ($contentLength) {
            $streamedResponse->headers->set('Content-Length', $contentLength);
        }

        return $streamedResponse;
    }

    /**
     * Maneja respuestas de error HTTP lanzando excepciones con mensajes adecuados.
     * @param mixed $response
     * @param int   $statusCode
     * @throws Exception
     */
    protected function handleErrorResponse($response, int $statusCode): void
    {
        $content = json_decode($response->getBody()->getContents(), true);
        $message = $content['message'] ?? 'Error inesperado';
        throw new Exception("Error HTTP {$statusCode}: {$message}");
    }
}