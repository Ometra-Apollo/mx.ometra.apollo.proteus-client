<?php

namespace Ometra\Apollo\Proteus;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Exception\RequestException;
use Ometra\Apollo\Proteus\Models\ProteusApp;
use Ometra\Apollo\Proteus\Services\ProteusContext;
use Ometra\Apollo\Proteus\Partials\DownloadMedia;
use Ometra\Apollo\Proteus\Partials\PayloadFormatting;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Cliente principal para consumir la API de Proteus.
 *
 * Esta clase expone métodos de alto nivel para gestionar media,
 * categorías, metadatos y transformaciones a través de la API.
 */
class Proteus extends BaseApiService
{
    use PayloadFormatting, DownloadMedia;

    /**
     * Crea una nueva instancia del cliente de Proteus.
     *
     * Puede recibir tenant_id y app_name como parámetros, o usará los del contexto
     * establecido por el middleware ProteusContextMiddleware.
     *
     * @param int|null $tenantId ID del tenant (opcional, usa contexto si no se proporciona)
     * @param string|null $appName Nombre de la aplicación (opcional, usa contexto si no se proporciona)
     * @param string|null $format
     * @throws Exception
     * @throws RuntimeException 
     */
    public function __construct(
        int|null $tenantId = null,
        string|null $appName = null,
        string|null $format = null
    ) {
        // Usar los parámetros proporcionados o el contexto
        $tenantId = $tenantId ?? ProteusContext::getTenantId();
        $appName = $appName ?? ProteusContext::getAppName();
        // Si se proporcionan tenant_id y app_name (ya sea por parámetro o contexto), buscar y descifrar el token
        if ($tenantId != null && $appName != null) {
            // Buscar la aplicación en la BD
            $app = ProteusApp::where('tenant_id', $tenantId)
                ->where('name', ucfirst(strtolower($appName)))
                ->first();

            if (!$app) {
                throw new Exception("No se encontró aplicación Proteus para tenant_id={$tenantId} y app_name={$appName}");
            }

            // Descifrar el token desde el hash almacenado
            try {
                $apiToken = ProteusApp::decryptToken($app->hash);
            } catch (\Exception $e) {
                throw new Exception("Error al descifrar el token: " . $e->getMessage());
            }
        } else {
            throw new Exception("No se proporcionó tenant_id o app_name, y no se encontró contexto válido.");
        }
        parent::__construct(
            Config::get('proteus.url'),
            $apiToken,
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
            return [
                'status' => 200,
                'message' => 'Lista de archivos obtenida con éxito.',
                'data' => [
                    [
                        'id' => '019d9380-2087-716d-ac97-8c5bb9121c83',
                        'name' => '05 - Deftones - Rickets',
                        'default_format' => [
                            'id' => 1,
                            'format' => 'mp3',
                            'media_id' => '019d9380-2087-716d-ac97-8c5bb9121c83',
                            'is_default' => 1,
                            'status' => 'completed',
                            'created_at' => '2026-04-15T23:35:55.000000Z',
                            'updated_at' => '2026-04-15T23:35:55.000000Z'
                        ],
                        'size' => 4008681,
                        'created_at' => '2026-04-15 17:35:40',
                        'category' => [
                            'id' => 'other',
                            'tenant_id' => 1,
                            'name' => 'Otro'
                        ],
                        'type' => 'audio',
                        'metadata' => [],
                        'available_formats' => [
                            [
                                'id' => 1,
                                'format' => 'mp3',
                                'media_id' => '019d9380-2087-716d-ac97-8c5bb9121c83',
                                'is_default' => 1,
                                'status' => 'completed',
                                'created_at' => '2026-04-15T23:35:55.000000Z',
                                'updated_at' => '2026-04-15T23:35:55.000000Z'
                            ]
                        ]
                    ],
                    [
                        'id' => '019d9380-591f-739a-a37c-ec42b2c15886',
                        'name' => '06 - Deftones - Be Quiet And Drive (Far Away)',
                        'default_format' => [
                            'id' => 2,
                            'format' => 'mp3',
                            'media_id' => '019d9380-591f-739a-a37c-ec42b2c15886',
                            'is_default' => 1,
                            'status' => 'completed',
                            'created_at' => '2026-04-15T23:36:51.000000Z',
                            'updated_at' => '2026-04-15T23:36:51.000000Z'
                        ],
                        'size' => 7736237,
                        'created_at' => '2026-04-15 17:35:55',
                        'category' => [
                            'id' => 'other',
                            'tenant_id' => 1,
                            'name' => 'Otro'
                        ],
                        'type' => 'audio',
                        'metadata' => [],
                        'available_formats' => [
                            [
                                'id' => 2,
                                'format' => 'mp3',
                                'media_id' => '019d9380-591f-739a-a37c-ec42b2c15886',
                                'is_default' => 1,
                                'status' => 'completed',
                                'created_at' => '2026-04-15T23:36:51.000000Z',
                                'updated_at' => '2026-04-15T23:36:51.000000Z'
                            ]
                        ]
                    ],
                    [
                        'id' => '019d9381-34e7-7343-a804-fd137130f07e',
                        'name' => '07 - Deftones - Lotion',
                        'default_format' => [
                            'id' => 3,
                            'format' => 'mp3',
                            'media_id' => '019d9381-34e7-7343-a804-fd137130f07e',
                            'is_default' => 1,
                            'status' => 'completed',
                            'created_at' => '2026-04-15T23:37:32.000000Z',
                            'updated_at' => '2026-04-15T23:37:32.000000Z'
                        ],
                        'size' => 5723397,
                        'created_at' => '2026-04-15 17:36:51',
                        'category' => [
                            'id' => 'other',
                            'tenant_id' => 1,
                            'name' => 'Otro'
                        ],
                        'type' => 'audio',
                        'metadata' => [],
                        'available_formats' => [
                            [
                                'id' => 3,
                                'format' => 'mp3',
                                'media_id' => '019d9381-34e7-7343-a804-fd137130f07e',
                                'is_default' => 1,
                                'status' => 'completed',
                                'created_at' => '2026-04-15T23:37:32.000000Z',
                                'updated_at' => '2026-04-15T23:37:32.000000Z'
                            ]
                        ]
                    ],
                    [
                        'id' => '019d9381-d2a3-7176-aad4-9dcdd60a0e8f',
                        'name' => '08 - Deftones - Dai The Flu',
                        'default_format' => null,
                        'size' => 6697264,
                        'created_at' => '2026-04-15 17:37:32',
                        'category' => [
                            'id' => 'other',
                            'tenant_id' => 1,
                            'name' => 'Otro'
                        ],
                        'type' => 'audio',
                        'metadata' => [],
                        'available_formats' => []
                    ]
                ]
            ];
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

            return [
                'status' => 200,
                'message' => 'Archivo obtenido con éxito.',
                'data' => [
                    'id' => '019d9380-2087-716d-ac97-8c5bb9121c83',
                    'name' => '05 - Deftones - Rickets',
                    'type' => 'audio',
                    'size' => 4008681,
                    'checksum' => null,
                    'created_at' => '2026-04-15T23:35:40.000000Z',
                    'updated_at' => '2026-04-15T23:35:40.000000Z',
                    'directory_id' => '4dce229ceba9f76841a63d15bbedc809be0da798',
                    'category_id' => 'other',
                    'metadata' => [],
                    'formats' => [
                        [
                            'id' => 1,
                            'format' => 'mp3',
                            'media_id' => '019d9380-2087-716d-ac97-8c5bb9121c83',
                            'is_default' => 1,
                            'status' => 'completed',
                            'created_at' => '2026-04-15T23:35:55.000000Z',
                            'updated_at' => '2026-04-15T23:35:55.000000Z'
                        ]
                    ],
                    'category' => [
                        'id' => 'other',
                        'tenant_id' => 1,
                        'name' => 'Otro'
                    ],
                    'default_format' => [
                        'id' => 1,
                        'format' => 'mp3',
                        'media_id' => '019d9380-2087-716d-ac97-8c5bb9121c83',
                        'is_default' => 1,
                        'status' => 'completed',
                        'created_at' => '2026-04-15T23:35:55.000000Z',
                        'updated_at' => '2026-04-15T23:35:55.000000Z'
                    ],
                    'tags' => [],
                    'logs' => [],
                    'uploader' => [
                        'uri_user' => '613557f0e87f4aa51eba7ad544a70711c9d466f4',
                        'name' => 'Erick Escobar',
                        'email' => 'eescobar@ometra.mx',
                        'tenant_id' => null
                    ]
                ]
            ];
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
    public function uploadFile(array $data): array
    {
        try {
            $multipartPayload = $this->prepareMultipart($data);
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
    public function mediaDelete(string $id): ?array
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
            return $this->request(method: 'POST', endpoint: 'media/' . $id_media . '/request-transformations', data: $data);
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
    public function metadataValuesFromKey(string $key): array
    {
        try {
            return $this->request(method: 'GET', endpoint: 'media/metadata/' . $key . '/values');
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
        try {
            $payload = $this->prepareMultipart($data);
            return $this->request(method: 'POST', endpoint: 'media/' . $id . '/metadata', data: $payload, format: 'multipart');
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
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
        try {
            $payload = $this->prepareMultipart($data);
            return $this->request(method: 'PUT', endpoint: 'media/' . $id . '/metadata', data: $payload, format: 'multipart');
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Elimina un metadato específico de un media.
     * @param string $id  
     * @param string $key 
     * @return array 
     * @throws Exception
     */
    public function metadataDelete(string $id, string $key): ?array
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
            return [
                'status' => 200,
                'message' => 'Lista de categorías obtenida correctamente.',
                'data' => [
                    [
                        'id' => '11',
                        'slug' => 'advertising',
                        'name' => 'Publicidad'
                    ],
                    [
                        'id' => '16',
                        'slug' => 'intern',
                        'name' => 'Interno'
                    ],
                    [
                        'id' => '1',
                        'slug' => 'music',
                        'name' => 'Música'
                    ],
                    [
                        'id' => '6',
                        'slug' => 'news',
                        'name' => 'Noticias'
                    ],
                    [
                        'id' => '21',
                        'slug' => 'other',
                        'name' => 'Otro'
                    ]
                ]
            ];
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
            return $this->request(method: 'PUT', endpoint: 'categories' . '/' . $id, data: $data);
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
    public function categoryDelete(string $id): ?array
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
    public function directoriesIndex(array $data): array
    {
        try {
            return [
                'status' => 200,
                'message' => 'Lista de directorios obtenida correctamente.',
                'data' => [
                    'directory' => [
                        'id' => '4dce229ceba9f76841a63d15bbedc809be0da798',
                        'tenant_id' => 1,
                        'name' => 'Ignis',
                        'is_application_directory' => 1,
                        'is_shareable' => 0,
                        'parent_id' => null,
                        'category_id' => null,
                        'preset_id' => null,
                        'created_at' => '2026-04-15T23:34:58.000000Z',
                        'updated_at' => '2026-04-15T23:34:58.000000Z',
                        'owner_id' => '4dce229ceba9f76841a63d15bbedc809be0da798',
                        'node_type' => 'user_root',
                        'items' => [
                            'current_page' => 1,
                            'data' => [
                                [
                                    'id' => '019d9380-2087-716d-ac97-8c5bb9121c83',
                                    'name' => '05 - Deftones - Rickets',
                                    'type' => 'audio',
                                    'size' => 4008681,
                                    'checksum' => null,
                                    'created_at' => '2026-04-15T23:35:40.000000Z',
                                    'updated_at' => '2026-04-15T23:35:40.000000Z',
                                    'directory_id' => '4dce229ceba9f76841a63d15bbedc809be0da798',
                                    'category_id' => 'other',
                                    'metadata' => [],
                                    'formats' => [
                                        [
                                            'id' => 1,
                                            'format' => 'mp3',
                                            'media_id' => '019d9380-2087-716d-ac97-8c5bb9121c83',
                                            'is_default' => 1,
                                            'status' => 'completed',
                                            'created_at' => '2026-04-15T23:35:55.000000Z',
                                            'updated_at' => '2026-04-15T23:35:55.000000Z'
                                        ]
                                    ],
                                    'category' => [
                                        'id' => 'other',
                                        'tenant_id' => 1,
                                        'name' => 'Otro'
                                    ],
                                    'default_format' => [
                                        'id' => 1,
                                        'format' => 'mp3',
                                        'media_id' => '019d9380-2087-716d-ac97-8c5bb9121c83',
                                        'is_default' => 1,
                                        'status' => 'completed',
                                        'created_at' => '2026-04-15T23:35:55.000000Z',
                                        'updated_at' => '2026-04-15T23:35:55.000000Z'
                                    ],
                                    'tags' => [],
                                    'logs' => [],
                                    'uploader' => [
                                        'uri_user' => '613557f0e87f4aa51eba7ad544a70711c9d466f4',
                                        'name' => 'Erick Escobar',
                                        'email' => 'eescobar@ometra.mx',
                                        'tenant_id' => null
                                    ]
                                ],
                                [
                                    'id' => '019d9380-591f-739a-a37c-ec42b2c15886',
                                    'name' => '06 - Deftones - Be Quiet And Drive (Far Away)',
                                    'type' => 'audio',
                                    'size' => 7736237,
                                    'checksum' => null,
                                    'created_at' => '2026-04-15T23:35:55.000000Z',
                                    'updated_at' => '2026-04-15T23:35:55.000000Z',
                                    'directory_id' => '4dce229ceba9f76841a63d15bbedc809be0da798',
                                    'category_id' => 'other',
                                    'metadata' => [],
                                    'formats' => [
                                        [
                                            'id' => 2,
                                            'format' => 'mp3',
                                            'media_id' => '019d9380-591f-739a-a37c-ec42b2c15886',
                                            'is_default' => 1,
                                            'status' => 'completed',
                                            'created_at' => '2026-04-15T23:36:51.000000Z',
                                            'updated_at' => '2026-04-15T23:36:51.000000Z'
                                        ]
                                    ],
                                    'category' => [
                                        'id' => 'other',
                                        'tenant_id' => 1,
                                        'name' => 'Otro'
                                    ],
                                    'default_format' => [
                                        'id' => 2,
                                        'format' => 'mp3',
                                        'media_id' => '019d9380-591f-739a-a37c-ec42b2c15886',
                                        'is_default' => 1,
                                        'status' => 'completed',
                                        'created_at' => '2026-04-15T23:36:51.000000Z',
                                        'updated_at' => '2026-04-15T23:36:51.000000Z'
                                    ],
                                    'tags' => [],
                                    'logs' => [],
                                    'uploader' => [
                                        'uri_user' => '613557f0e87f4aa51eba7ad544a70711c9d466f4',
                                        'name' => 'Erick Escobar',
                                        'email' => 'eescobar@ometra.mx',
                                        'tenant_id' => null
                                    ]
                                ],
                                [
                                    'id' => '019d9381-34e7-7343-a804-fd137130f07e',
                                    'name' => '07 - Deftones - Lotion',
                                    'type' => 'audio',
                                    'size' => 5723397,
                                    'checksum' => null,
                                    'created_at' => '2026-04-15T23:36:51.000000Z',
                                    'updated_at' => '2026-04-15T23:36:51.000000Z',
                                    'directory_id' => '4dce229ceba9f76841a63d15bbedc809be0da798',
                                    'category_id' => 'other',
                                    'metadata' => [],
                                    'formats' => [
                                        [
                                            'id' => 3,
                                            'format' => 'mp3',
                                            'media_id' => '019d9381-34e7-7343-a804-fd137130f07e',
                                            'is_default' => 1,
                                            'status' => 'completed',
                                            'created_at' => '2026-04-15T23:37:32.000000Z',
                                            'updated_at' => '2026-04-15T23:37:32.000000Z'
                                        ]
                                    ],
                                    'category' => [
                                        'id' => 'other',
                                        'tenant_id' => 1,
                                        'name' => 'Otro'
                                    ],
                                    'default_format' => [
                                        'id' => 3,
                                        'format' => 'mp3',
                                        'media_id' => '019d9381-34e7-7343-a804-fd137130f07e',
                                        'is_default' => 1,
                                        'status' => 'completed',
                                        'created_at' => '2026-04-15T23:37:32.000000Z',
                                        'updated_at' => '2026-04-15T23:37:32.000000Z'
                                    ],
                                    'tags' => [],
                                    'logs' => [],
                                    'uploader' => [
                                        'uri_user' => '613557f0e87f4aa51eba7ad544a70711c9d466f4',
                                        'name' => 'Erick Escobar',
                                        'email' => 'eescobar@ometra.mx',
                                        'tenant_id' => null
                                    ]
                                ],
                                [
                                    'id' => '019d9381-d2a3-7176-aad4-9dcdd60a0e8f',
                                    'name' => '08 - Deftones - Dai The Flu',
                                    'type' => 'audio',
                                    'size' => 6697264,
                                    'checksum' => null,
                                    'created_at' => '2026-04-15T23:37:32.000000Z',
                                    'updated_at' => '2026-04-15T23:37:32.000000Z',
                                    'directory_id' => '4dce229ceba9f76841a63d15bbedc809be0da798',
                                    'category_id' => 'other',
                                    'metadata' => [],
                                    'formats' => [],
                                    'category' => [
                                        'id' => 'other',
                                        'tenant_id' => 1,
                                        'name' => 'Otro'
                                    ],
                                    'default_format' => null,
                                    'tags' => [],
                                    'logs' => [],
                                    'uploader' => [
                                        'uri_user' => '613557f0e87f4aa51eba7ad544a70711c9d466f4',
                                        'name' => 'Erick Escobar',
                                        'email' => 'eescobar@ometra.mx',
                                        'tenant_id' => null
                                    ]
                                ]
                            ],
                            'first_page_url' => 'http://127.0.0.1:8000/api/directories?page=1',
                            'from' => 1,
                            'last_page' => 1,
                            'last_page_url' => 'http://127.0.0.1:8000/api/directories?page=1',
                            'links' => [
                                [
                                    'url' => null,
                                    'label' => '&laquo; Previous',
                                    'page' => null,
                                    'active' => false
                                ],
                                [
                                    'url' => 'http://127.0.0.1:8000/api/directories?page=1',
                                    'label' => '1',
                                    'page' => 1,
                                    'active' => true
                                ],
                                [
                                    'url' => null,
                                    'label' => 'Next &raquo;',
                                    'page' => null,
                                    'active' => false
                                ]
                            ],
                            'next_page_url' => null,
                            'path' => 'http://127.0.0.1:8000/api/directories',
                            'per_page' => 10,
                            'prev_page_url' => null,
                            'to' => 4,
                            'total' => 4
                        ],
                        'preset' => null,
                        'media' => [
                            [
                                'id' => '019d9380-2087-716d-ac97-8c5bb9121c83',
                                'name' => '05 - Deftones - Rickets',
                                'type' => 'audio',
                                'size' => 4008681,
                                'checksum' => null,
                                'created_at' => '2026-04-15T23:35:40.000000Z',
                                'updated_at' => '2026-04-15T23:35:40.000000Z',
                                'directory_id' => '4dce229ceba9f76841a63d15bbedc809be0da798',
                                'category_id' => 'other',
                                'metadata' => [],
                                'formats' => [
                                    [
                                        'id' => 1,
                                        'format' => 'mp3',
                                        'media_id' => '019d9380-2087-716d-ac97-8c5bb9121c83',
                                        'is_default' => 1,
                                        'status' => 'completed',
                                        'created_at' => '2026-04-15T23:35:55.000000Z',
                                        'updated_at' => '2026-04-15T23:35:55.000000Z'
                                    ]
                                ],
                                'category' => [
                                    'id' => 'other',
                                    'tenant_id' => 1,
                                    'name' => 'Otro'
                                ],
                                'default_format' => [
                                    'id' => 1,
                                    'format' => 'mp3',
                                    'media_id' => '019d9380-2087-716d-ac97-8c5bb9121c83',
                                    'is_default' => 1,
                                    'status' => 'completed',
                                    'created_at' => '2026-04-15T23:35:55.000000Z',
                                    'updated_at' => '2026-04-15T23:35:55.000000Z'
                                ],
                                'tags' => [],
                                'logs' => [],
                                'uploader' => [
                                    'uri_user' => '613557f0e87f4aa51eba7ad544a70711c9d466f4',
                                    'name' => 'Erick Escobar',
                                    'email' => 'eescobar@ometra.mx',
                                    'tenant_id' => null
                                ]
                            ],
                            [
                                'id' => '019d9380-591f-739a-a37c-ec42b2c15886',
                                'name' => '06 - Deftones - Be Quiet And Drive (Far Away)',
                                'type' => 'audio',
                                'size' => 7736237,
                                'checksum' => null,
                                'created_at' => '2026-04-15T23:35:55.000000Z',
                                'updated_at' => '2026-04-15T23:35:55.000000Z',
                                'directory_id' => '4dce229ceba9f76841a63d15bbedc809be0da798',
                                'category_id' => 'other',
                                'metadata' => [],
                                'formats' => [
                                    [
                                        'id' => 2,
                                        'format' => 'mp3',
                                        'media_id' => '019d9380-591f-739a-a37c-ec42b2c15886',
                                        'is_default' => 1,
                                        'status' => 'completed',
                                        'created_at' => '2026-04-15T23:36:51.000000Z',
                                        'updated_at' => '2026-04-15T23:36:51.000000Z'
                                    ]
                                ],
                                'category' => [
                                    'id' => 'other',
                                    'tenant_id' => 1,
                                    'name' => 'Otro'
                                ],
                                'default_format' => [
                                    'id' => 2,
                                    'format' => 'mp3',
                                    'media_id' => '019d9380-591f-739a-a37c-ec42b2c15886',
                                    'is_default' => 1,
                                    'status' => 'completed',
                                    'created_at' => '2026-04-15T23:36:51.000000Z',
                                    'updated_at' => '2026-04-15T23:36:51.000000Z'
                                ],
                                'tags' => [],
                                'logs' => [],
                                'uploader' => [
                                    'uri_user' => '613557f0e87f4aa51eba7ad544a70711c9d466f4',
                                    'name' => 'Erick Escobar',
                                    'email' => 'eescobar@ometra.mx',
                                    'tenant_id' => null
                                ]
                            ],
                            [
                                'id' => '019d9381-34e7-7343-a804-fd137130f07e',
                                'name' => '07 - Deftones - Lotion',
                                'type' => 'audio',
                                'size' => 5723397,
                                'checksum' => null,
                                'created_at' => '2026-04-15T23:36:51.000000Z',
                                'updated_at' => '2026-04-15T23:36:51.000000Z',
                                'directory_id' => '4dce229ceba9f76841a63d15bbedc809be0da798',
                                'category_id' => 'other',
                                'metadata' => [],
                                'formats' => [
                                    [
                                        'id' => 3,
                                        'format' => 'mp3',
                                        'media_id' => '019d9381-34e7-7343-a804-fd137130f07e',
                                        'is_default' => 1,
                                        'status' => 'completed',
                                        'created_at' => '2026-04-15T23:37:32.000000Z',
                                        'updated_at' => '2026-04-15T23:37:32.000000Z'
                                    ]
                                ],
                                'category' => [
                                    'id' => 'other',
                                    'tenant_id' => 1,
                                    'name' => 'Otro'
                                ],
                                'default_format' => [
                                    'id' => 3,
                                    'format' => 'mp3',
                                    'media_id' => '019d9381-34e7-7343-a804-fd137130f07e',
                                    'is_default' => 1,
                                    'status' => 'completed',
                                    'created_at' => '2026-04-15T23:37:32.000000Z',
                                    'updated_at' => '2026-04-15T23:37:32.000000Z'
                                ],
                                'tags' => [],
                                'logs' => [],
                                'uploader' => [
                                    'uri_user' => '613557f0e87f4aa51eba7ad544a70711c9d466f4',
                                    'name' => 'Erick Escobar',
                                    'email' => 'eescobar@ometra.mx',
                                    'tenant_id' => null
                                ]
                            ],
                            [
                                'id' => '019d9381-d2a3-7176-aad4-9dcdd60a0e8f',
                                'name' => '08 - Deftones - Dai The Flu',
                                'type' => 'audio',
                                'size' => 6697264,
                                'checksum' => null,
                                'created_at' => '2026-04-15T23:37:32.000000Z',
                                'updated_at' => '2026-04-15T23:37:32.000000Z',
                                'directory_id' => '4dce229ceba9f76841a63d15bbedc809be0da798',
                                'category_id' => 'other',
                                'metadata' => [],
                                'formats' => [],
                                'category' => [
                                    'id' => 'other',
                                    'tenant_id' => 1,
                                    'name' => 'Otro'
                                ],
                                'default_format' => null,
                                'tags' => [],
                                'logs' => [],
                                'uploader' => [
                                    'uri_user' => '613557f0e87f4aa51eba7ad544a70711c9d466f4',
                                    'name' => 'Erick Escobar',
                                    'email' => 'eescobar@ometra.mx',
                                    'tenant_id' => null
                                ]
                            ]
                        ],
                        'groups' => [],
                        'tags' => [],
                        'children' => []
                    ],
                    'tags' => [],
                    'results' => [
                        [
                            'id' => '019d9380-2087-716d-ac97-8c5bb9121c83',
                            'name' => '05 - Deftones - Rickets',
                            'type' => 'audio',
                            'size' => 4008681,
                            'checksum' => null,
                            'created_at' => '2026-04-15T23:35:40.000000Z',
                            'updated_at' => '2026-04-15T23:35:40.000000Z',
                            'directory_id' => '4dce229ceba9f76841a63d15bbedc809be0da798',
                            'category_id' => 'other',
                            'metadata' => [],
                            'formats' => [
                                [
                                    'id' => 1,
                                    'format' => 'mp3',
                                    'media_id' => '019d9380-2087-716d-ac97-8c5bb9121c83',
                                    'is_default' => 1,
                                    'status' => 'completed',
                                    'created_at' => '2026-04-15T23:35:55.000000Z',
                                    'updated_at' => '2026-04-15T23:35:55.000000Z'
                                ]
                            ],
                            'category' => [
                                'id' => 'other',
                                'tenant_id' => 1,
                                'name' => 'Otro'
                            ],
                            'default_format' => [
                                'id' => 1,
                                'format' => 'mp3',
                                'media_id' => '019d9380-2087-716d-ac97-8c5bb9121c83',
                                'is_default' => 1,
                                'status' => 'completed',
                                'created_at' => '2026-04-15T23:35:55.000000Z',
                                'updated_at' => '2026-04-15T23:35:55.000000Z'
                            ],
                            'tags' => [],
                            'logs' => [],
                            'uploader' => [
                                'uri_user' => '613557f0e87f4aa51eba7ad544a70711c9d466f4',
                                'name' => 'Erick Escobar',
                                'email' => 'eescobar@ometra.mx',
                                'tenant_id' => null
                            ]
                        ],
                        [
                            'id' => '019d9380-591f-739a-a37c-ec42b2c15886',
                            'name' => '06 - Deftones - Be Quiet And Drive (Far Away)',
                            'type' => 'audio',
                            'size' => 7736237,
                            'checksum' => null,
                            'created_at' => '2026-04-15T23:35:55.000000Z',
                            'updated_at' => '2026-04-15T23:35:55.000000Z',
                            'directory_id' => '4dce229ceba9f76841a63d15bbedc809be0da798',
                            'category_id' => 'other',
                            'metadata' => [],
                            'formats' => [
                                [
                                    'id' => 2,
                                    'format' => 'mp3',
                                    'media_id' => '019d9380-591f-739a-a37c-ec42b2c15886',
                                    'is_default' => 1,
                                    'status' => 'completed',
                                    'created_at' => '2026-04-15T23:36:51.000000Z',
                                    'updated_at' => '2026-04-15T23:36:51.000000Z'
                                ]
                            ],
                            'category' => [
                                'id' => 'other',
                                'tenant_id' => 1,
                                'name' => 'Otro'
                            ],
                            'default_format' => [
                                'id' => 2,
                                'format' => 'mp3',
                                'media_id' => '019d9380-591f-739a-a37c-ec42b2c15886',
                                'is_default' => 1,
                                'status' => 'completed',
                                'created_at' => '2026-04-15T23:36:51.000000Z',
                                'updated_at' => '2026-04-15T23:36:51.000000Z'
                            ],
                            'tags' => [],
                            'logs' => [],
                            'uploader' => [
                                'uri_user' => '613557f0e87f4aa51eba7ad544a70711c9d466f4',
                                'name' => 'Erick Escobar',
                                'email' => 'eescobar@ometra.mx',
                                'tenant_id' => null
                            ]
                        ],
                        [
                            'id' => '019d9381-34e7-7343-a804-fd137130f07e',
                            'name' => '07 - Deftones - Lotion',
                            'type' => 'audio',
                            'size' => 5723397,
                            'checksum' => null,
                            'created_at' => '2026-04-15T23:36:51.000000Z',
                            'updated_at' => '2026-04-15T23:36:51.000000Z',
                            'directory_id' => '4dce229ceba9f76841a63d15bbedc809be0da798',
                            'category_id' => 'other',
                            'metadata' => [],
                            'formats' => [
                                [
                                    'id' => 3,
                                    'format' => 'mp3',
                                    'media_id' => '019d9381-34e7-7343-a804-fd137130f07e',
                                    'is_default' => 1,
                                    'status' => 'completed',
                                    'created_at' => '2026-04-15T23:37:32.000000Z',
                                    'updated_at' => '2026-04-15T23:37:32.000000Z'
                                ]
                            ],
                            'category' => [
                                'id' => 'other',
                                'tenant_id' => 1,
                                'name' => 'Otro'
                            ],
                            'default_format' => [
                                'id' => 3,
                                'format' => 'mp3',
                                'media_id' => '019d9381-34e7-7343-a804-fd137130f07e',
                                'is_default' => 1,
                                'status' => 'completed',
                                'created_at' => '2026-04-15T23:37:32.000000Z',
                                'updated_at' => '2026-04-15T23:37:32.000000Z'
                            ],
                            'tags' => [],
                            'logs' => [],
                            'uploader' => [
                                'uri_user' => '613557f0e87f4aa51eba7ad544a70711c9d466f4',
                                'name' => 'Erick Escobar',
                                'email' => 'eescobar@ometra.mx',
                                'tenant_id' => null
                            ]
                        ],
                        [
                            'id' => '019d9381-d2a3-7176-aad4-9dcdd60a0e8f',
                            'name' => '08 - Deftones - Dai The Flu',
                            'type' => 'audio',
                            'size' => 6697264,
                            'checksum' => null,
                            'created_at' => '2026-04-15T23:37:32.000000Z',
                            'updated_at' => '2026-04-15T23:37:32.000000Z',
                            'directory_id' => '4dce229ceba9f76841a63d15bbedc809be0da798',
                            'category_id' => 'other',
                            'metadata' => [],
                            'formats' => [],
                            'category' => [
                                'id' => 'other',
                                'tenant_id' => 1,
                                'name' => 'Otro'
                            ],
                            'default_format' => null,
                            'tags' => [],
                            'logs' => [],
                            'uploader' => [
                                'uri_user' => '613557f0e87f4aa51eba7ad544a70711c9d466f4',
                                'name' => 'Erick Escobar',
                                'email' => 'eescobar@ometra.mx',
                                'tenant_id' => null
                            ]
                        ]
                    ],
                    'directories' => [],
                    'metadataKeys' => [
                        'video' => [
                            'Título',
                            'Artista',
                            'Género',
                            'Duración (segundos)',
                            'Duración'
                        ],
                        'audio' => [
                            'Título',
                            'Artista',
                            'Álbum',
                            'Género',
                            'Año'
                        ],
                        'image' => [
                            'Resolución horizontal',
                            'Resolución vertical'
                        ]
                    ]
                ]
            ];
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
            return [
                'status' => 200,
                'message' => 'Directorio obtenido correctamente.',
                'data' => [
                    'directory' => [
                        'id' => '4dce229ceba9f76841a63d15bbedc809be0da798',
                        'name' => 'Ignis',
                        'category_id' => null,
                        'preset' => null,
                        'media' => [
                            [
                                'id' => '019d9380-2087-716d-ac97-8c5bb9121c83',
                                'name' => '05 - Deftones - Rickets',
                                'type' => 'audio',
                                'size' => 4008681,
                                'checksum' => null,
                                'created_at' => '2026-04-15T23:35:40.000000Z',
                                'updated_at' => '2026-04-15T23:35:40.000000Z',
                                'directory_id' => '4dce229ceba9f76841a63d15bbedc809be0da798',
                                'category_id' => 'other',
                                'metadata' => [],
                                'formats' => [
                                    [
                                        'id' => 1,
                                        'format' => 'mp3',
                                        'media_id' => '019d9380-2087-716d-ac97-8c5bb9121c83',
                                        'is_default' => 1,
                                        'status' => 'completed',
                                        'created_at' => '2026-04-15T23:35:55.000000Z',
                                        'updated_at' => '2026-04-15T23:35:55.000000Z'
                                    ]
                                ],
                                'category' => [
                                    'id' => 'other',
                                    'tenant_id' => 1,
                                    'name' => 'Otro'
                                ],
                                'default_format' => [
                                    'id' => 1,
                                    'format' => 'mp3',
                                    'media_id' => '019d9380-2087-716d-ac97-8c5bb9121c83',
                                    'is_default' => 1,
                                    'status' => 'completed',
                                    'created_at' => '2026-04-15T23:35:55.000000Z',
                                    'updated_at' => '2026-04-15T23:35:55.000000Z'
                                ],
                                'tags' => [],
                                'logs' => [],
                                'uploader' => [
                                    'uri_user' => '613557f0e87f4aa51eba7ad544a70711c9d466f4',
                                    'name' => 'Erick Escobar',
                                    'email' => 'eescobar@ometra.mx',
                                    'tenant_id' => null
                                ]
                            ],
                            [
                                'id' => '019d9380-591f-739a-a37c-ec42b2c15886',
                                'name' => '06 - Deftones - Be Quiet And Drive (Far Away)',
                                'type' => 'audio',
                                'size' => 7736237,
                                'checksum' => null,
                                'created_at' => '2026-04-15T23:35:55.000000Z',
                                'updated_at' => '2026-04-15T23:35:55.000000Z',
                                'directory_id' => '4dce229ceba9f76841a63d15bbedc809be0da798',
                                'category_id' => 'other',
                                'metadata' => [],
                                'formats' => [
                                    [
                                        'id' => 2,
                                        'format' => 'mp3',
                                        'media_id' => '019d9380-591f-739a-a37c-ec42b2c15886',
                                        'is_default' => 1,
                                        'status' => 'completed',
                                        'created_at' => '2026-04-15T23:36:51.000000Z',
                                        'updated_at' => '2026-04-15T23:36:51.000000Z'
                                    ]
                                ],
                                'category' => [
                                    'id' => 'other',
                                    'tenant_id' => 1,
                                    'name' => 'Otro'
                                ],
                                'default_format' => [
                                    'id' => 2,
                                    'format' => 'mp3',
                                    'media_id' => '019d9380-591f-739a-a37c-ec42b2c15886',
                                    'is_default' => 1,
                                    'status' => 'completed',
                                    'created_at' => '2026-04-15T23:36:51.000000Z',
                                    'updated_at' => '2026-04-15T23:36:51.000000Z'
                                ],
                                'tags' => [],
                                'logs' => [],
                                'uploader' => [
                                    'uri_user' => '613557f0e87f4aa51eba7ad544a70711c9d466f4',
                                    'name' => 'Erick Escobar',
                                    'email' => 'eescobar@ometra.mx',
                                    'tenant_id' => null
                                ]
                            ],
                            [
                                'id' => '019d9381-34e7-7343-a804-fd137130f07e',
                                'name' => '07 - Deftones - Lotion',
                                'type' => 'audio',
                                'size' => 5723397,
                                'checksum' => null,
                                'created_at' => '2026-04-15T23:36:51.000000Z',
                                'updated_at' => '2026-04-15T23:36:51.000000Z',
                                'directory_id' => '4dce229ceba9f76841a63d15bbedc809be0da798',
                                'category_id' => 'other',
                                'metadata' => [],
                                'formats' => [
                                    [
                                        'id' => 3,
                                        'format' => 'mp3',
                                        'media_id' => '019d9381-34e7-7343-a804-fd137130f07e',
                                        'is_default' => 1,
                                        'status' => 'completed',
                                        'created_at' => '2026-04-15T23:37:32.000000Z',
                                        'updated_at' => '2026-04-15T23:37:32.000000Z'
                                    ]
                                ],
                                'category' => [
                                    'id' => 'other',
                                    'tenant_id' => 1,
                                    'name' => 'Otro'
                                ],
                                'default_format' => [
                                    'id' => 3,
                                    'format' => 'mp3',
                                    'media_id' => '019d9381-34e7-7343-a804-fd137130f07e',
                                    'is_default' => 1,
                                    'status' => 'completed',
                                    'created_at' => '2026-04-15T23:37:32.000000Z',
                                    'updated_at' => '2026-04-15T23:37:32.000000Z'
                                ],
                                'tags' => [],
                                'logs' => [],
                                'uploader' => [
                                    'uri_user' => '613557f0e87f4aa51eba7ad544a70711c9d466f4',
                                    'name' => 'Erick Escobar',
                                    'email' => 'eescobar@ometra.mx',
                                    'tenant_id' => null
                                ]
                            ],
                            [
                                'id' => '019d9381-d2a3-7176-aad4-9dcdd60a0e8f',
                                'name' => '08 - Deftones - Dai The Flu',
                                'type' => 'audio',
                                'size' => 6697264,
                                'checksum' => null,
                                'created_at' => '2026-04-15T23:37:32.000000Z',
                                'updated_at' => '2026-04-15T23:37:32.000000Z',
                                'directory_id' => '4dce229ceba9f76841a63d15bbedc809be0da798',
                                'category_id' => 'other',
                                'metadata' => [],
                                'formats' => [],
                                'category' => [
                                    'id' => 'other',
                                    'tenant_id' => 1,
                                    'name' => 'Otro'
                                ],
                                'default_format' => null,
                                'tags' => [],
                                'logs' => [],
                                'uploader' => [
                                    'uri_user' => '613557f0e87f4aa51eba7ad544a70711c9d466f4',
                                    'name' => 'Erick Escobar',
                                    'email' => 'eescobar@ometra.mx',
                                    'tenant_id' => null
                                ]
                            ]
                        ],
                        'images_count' => 0,
                        'videos_count' => 0,
                        'audios_count' => 4,
                        'parent_id' => null,
                        'children' => [],
                        'tags' => [],
                        'is_personal' => false,
                        'is_shareable' => 0,
                        'groups' => [],
                        'users' => [],
                        'groups_count' => 0,
                        'users_count' => 0,
                        'metadata' => [],
                        'metadata_entries' => [],
                        'is_application_directory' => 1,
                        'owner' => '',
                        'node_type' => 'user_root'
                    ],
                    'tags' => [],
                    'breadcrumbs' => [
                        [
                            'href' => 'http://127.0.0.1:8000/directories/4dce229ceba9f76841a63d15bbedc809be0da798',
                            'label' => 'Ignis'
                        ]
                    ],
                    'metadataKeys' => [
                        'video' => [
                            'Título',
                            'Artista',
                            'Género',
                            'Duración (segundos)',
                            'Duración'
                        ],
                        'audio' => [
                            'Título',
                            'Artista',
                            'Álbum',
                            'Género',
                            'Año'
                        ],
                        'image' => [
                            'Resolución horizontal',
                            'Resolución vertical'
                        ]
                    ],
                    'permissions' => [
                        'read' => true,
                        'write' => true,
                        'delete' => true,
                        'share' => false
                    ],
                    'categories' => [
                        [
                            'id' => 'other',
                            'tenant_id' => 1,
                            'name' => 'Otro'
                        ]
                    ]
                ]
            ];
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
    public function directoryDelete(string $id): ?array
    {
        try {
            return [
                'status' => 200,
                'message' => 'Carpeta eliminada correctamente.'
            ];
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
            return $this->request(method: 'PUT', endpoint: 'directories/' . $id, data: $data);
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
        return $this->download($id, $ext, $maxRetries, $retryDelaySeconds);
    }

    /**
     * Descarga un media y lo guarda en el sistema de ficheros configurado
     * en Laravel a través del facade `Storage`.
     *
     * @param string $id        
     * @param string $ext
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

            $stream = $response->getBody()->detach();

            if (is_resource($stream)) {
                Storage::putStream($id, $stream);
                fclose($stream);
            }
        } catch (RequestException $e) {
            throw new Exception('Error al guardar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene la información de preset asociada a un media.
     * @param string $directory_id 
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
     * @param string $directory_id
     * @param array $data
     * @return array
     */
    public function presetStore(string $directory_id, array $data): array
    {
        try {
            return $this->request(method: 'POST', endpoint: 'directories/' . $directory_id . '/presets', data: $data);
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
    public function presetDelete(string $directory_id, string $preset_id): ?array
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
            return $this->request(method: 'PUT', endpoint: 'directories/' . $directory_id . '/presets/' . $preset_id, data: $data);
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
}
