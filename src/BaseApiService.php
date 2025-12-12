<?php

namespace Apollo\Proteus;

use Exception;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;
use RuntimeException;

class BaseApiService
{
    protected Client $client;
    const CONTENT_TYPE = 'audio/mpeg';

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

    protected function requestDownload(string $method, string $endpoint, array $data = [], string $format = 'json')
    {
        try {
            return  $this->client->request(
                method: $method,
                uri: $endpoint,
                options: [$format => $data]
            );
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
