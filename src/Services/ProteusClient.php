<?php

namespace Ometra\Apollo\Proteus\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Ometra\Apollo\Proteus\Exceptions\ProteusException;

/**
 * HTTP Client para consumir la API de Proteus.
 * 
 * Automáticamente incluye:
 * - Token de autenticación
 * - Tenant ID (desde ProteusContext)
 * - Headers necesarios
 */
class ProteusClient
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('proteus.base_url', 'http://localhost:8000'), '/');
        $this->token = config('proteus.app_token', '');

        if (!$this->token) {
            throw new ProteusException('Proteus token not configured. Set PROTEUS_APP_TOKEN in .env');
        }
    }

    /**
     * Make a GET request.
     */
    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('get', $endpoint, ['query' => $query]);
    }

    /**
     * Make a POST request.
     */
    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('post', $endpoint, ['json' => $data]);
    }

    /**
     * Make a PUT request.
     */
    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('put', $endpoint, ['json' => $data]);
    }

    /**
     * Make a DELETE request.
     */
    public function delete(string $endpoint): array
    {
        return $this->request('delete', $endpoint);
    }

    /**
     * Make an HTTP request with proper headers and error handling.
     */
    private function request(string $method, string $endpoint, array $options = []): array
    {
        try {
            $url = $this->baseUrl . '/' . ltrim($endpoint, '/');

            $client = $this->createClient()
                ->{$method}($url, $options);

            if (!$client->successful()) {
                throw new ProteusException(
                    "Proteus API error: {$client->status()}",
                    $client->status(),
                    $client->json()
                );
            }

            return $client->json();
        } catch (\Exception $e) {
            if ($e instanceof ProteusException) {
                throw $e;
            }

            throw new ProteusException(
                "Proteus request failed: {$e->getMessage()}",
                0,
                null,
                $e
            );
        }
    }

    /**
     * Create HTTP client with headers.
     */
    private function createClient(): PendingRequest
    {
        $headers = [
            'Authorization' => "Bearer {$this->token}",
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        // Add tenant ID if available
        if (ProteusContext::getTenantId()) {
            $headers['X-Tenant-ID'] = app(\Equidna\BeeHive\Tenancy\TenantContext:class)->get();
        }

        return Http::timeout(config('proteus.timeout', 30))
            ->retry(
                config('proteus.retries', 3),
                config('proteus.retry_delay', 100)
            )
            ->withHeaders($headers);
    }

    /**
     * Get application categories.
     */
    public function getCategories(): array
    {
        return $this->get('/api/categories');
    }

    /**
     * Get playlist data by ID.
     */
    public function getPlaylistData(string $id): array
    {
        return $this->get("/api/playlists/{$id}");
    }

    /**
     * Get all playlists.
     */
    public function getPlaylists(array $query = []): array
    {
        return $this->get('/api/playlists', $query);
    }

    /**
     * Create a new playlist.
     */
    public function createPlaylist(array $data): array
    {
        return $this->post('/api/playlists', $data);
    }

    /**
     * Update a playlist.
     */
    public function updatePlaylist(string $id, array $data): array
    {
        return $this->put("/api/playlists/{$id}", $data);
    }

    /**
     * Delete a playlist.
     */
    public function deletePlaylist(string $id): array
    {
        return $this->delete("/api/playlists/{$id}");
    }
}
