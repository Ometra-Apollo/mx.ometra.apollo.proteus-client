<?php

namespace Ometra\Apollo\Proteus;

use Exception;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;
use RuntimeException;

/**
 * Class BaseApiService
 *
 * Servicio base para consumir la API de Proteus.
 * Se encarga de inicializar el cliente HTTP de Guzzle y
 * proveer métodos de ayuda para realizar peticiones.
 *
 * @package Ometra\Apollo\Proteus
 */
class BaseApiService
{
    protected Client $client;
    const CONTENT_TYPE = 'audio/mpeg';

    /**
     * Crea una nueva instancia del servicio base para la API.
     *
     * @param string      $baseUrl URL base del servicio Proteus.
     * @param string      $token   Token de autenticación Bearer.
     * @param string|null $format  Formato del contenido a enviar
     *                             (null => application/json, otro => audio/mpeg).
     *
     * @throws RuntimeException Si la URL base o el token no están configurados.
     */
    public function __construct(string $baseUrl, string $token, string|null $format = null)
    {
        if (empty($baseUrl) || empty($token)) {
            throw new RuntimeException("The base URL or token is not set.");
        }

        if (is_null($format)) {
            $contentType = 'application/json';
        } else {
            $contentType =  self::CONTENT_TYPE;
        }
        $this->client = new Client([
            'base_uri' => $baseUrl,
            'headers' => [
                'Authorization' => "Bearer {$token}",
                'Content-Type' => $contentType,
            ],
        ]);
    }

    /**
     * Realiza una petición HTTP estándar a la API de Proteus.
     *
     * @param string $method   Verbo HTTP (GET, POST, PUT, DELETE, etc.).
     * @param string $endpoint Endpoint relativo dentro de la API.
     * @param array  $data     Datos a enviar en la petición.
     * @param string $format   Formato de envío (por defecto `json`, ej. `multipart`).
     *
     * @return array Respuesta decodificada en arreglo asociativo.
     *
     * @throws Exception Si ocurre un error de red o HTTP.
     */
    protected function request(string $method, string $endpoint, array $data = [], string $format = 'json')
    {
        try {
            $response = $this->client->request(
                method: $method,
                uri: $endpoint,
                options: [$format => $data]
            );
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }

        return json_decode($response->getBody(), true);
    }

    /**
     * Realiza una petición HTTP y retorna la respuesta sin decodificar,
     * pensada para descargas de archivos o streams.
     *
     * @param string $method   Verbo HTTP (GET, POST, etc.).
     * @param string $endpoint Endpoint relativo dentro de la API.
     * @param array  $data     Datos a enviar en la petición.
     * @param string $format   Formato de envío (por defecto `json`, ej. `stream`).
     *
     * @return \Psr\Http\Message\ResponseInterface Respuesta cruda de Guzzle.
     *
     * @throws Exception Si ocurre un error de red o HTTP.
     */
    protected function requestDownload(string $method, string $endpoint, array $data = [], string $format = 'default')
    {
        try {
            $options = [
                'query' => $data,
                'stream' => ($format === 'stream'),
            ];

            return $this->client->request(
                method: $method,
                uri: $endpoint,
                options: $options
            );
        } catch (RequestException $e) {
            throw new Exception("Error de conexión en Proteuss: " . $e->getMessage());
        }
    }
}
