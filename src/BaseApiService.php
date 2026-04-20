<?php

namespace Ometra\Apollo\Proteus;

use Exception;
use RuntimeException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

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
    public function __construct()
    {
        $credencials = $this->getCredentials();
        $this->client = new Client($credencials);
    }


    public function getCredentials(): array
    {
        $baseUrl = config('proteus.base_url');
        if (!$baseUrl) {
            throw new RuntimeException('Proteus base URL not configured. Set PROTEUS_BASE_URL in environment variables.');
        }   
        $contextClass = config('proteus.context_class');
        $context = app($contextClass);
        $tenantId = $context->get();
        if ($tenantId === null) {
            throw new RuntimeException("Tenant ID not found in context. Please validate the PROTEUS_CONTEXT_CLASS environment variable.");
        }

        $apiToken = config('proteus.app_token');
        if ($apiToken == null) {
            throw new RuntimeException("App token not found in configuration. Please validate the PROTEUS_APP_TOKEN environment variable.");
        }

        return [
            'base_uri' => $baseUrl,
            'headers' => [
                'Authorization' => "Bearer {$apiToken}",
                'X-Tenant-ID' => $tenantId,
                'Content-Type' => 'application/json',
            ],
        ];
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
     * @throws RuntimeException Si ocurre un error de red o HTTP.
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
            throw new RuntimeException($e->getMessage());
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
     * @throws RuntimeException Si ocurre un error de red o HTTP.
     */
    protected function requestDownload(string $method, string $endpoint, array $data = [], string $format = 'default')
    {

        $contextClass = config('proteus.context_class');
        $context = app($contextClass);
        $tenantId = $context->get();
        if ($tenantId === null) {
            throw new RuntimeException("Tenant ID not found in context. Please validate the PROTEUS_CONTEXT_CLASS environment variable.");
        }
        
        try {
            $options = [
                'query' => $data,
                'stream' => ($format === 'stream'),
                'headers' => [
                    'X-Tenant-ID' => $tenantId,
                ],
            ];

            return $this->client->request(
                method: $method,
                uri: $endpoint,
                options: $options
            );
        } catch (RequestException $e) {
            throw new RuntimeException("Error de conexión en Proteus: " . $e->getMessage());
        }
    }
}
