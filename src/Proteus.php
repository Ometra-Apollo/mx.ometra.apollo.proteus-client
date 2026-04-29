<?php

namespace Ometra\Apollo\Proteus;

use BadMethodCallException;
use Ometra\Apollo\Proteus\Api\MediaApi;
use Ometra\Apollo\Proteus\Api\PresetsApi;
use Ometra\Apollo\Proteus\Api\MetadataApi;
use Ometra\Apollo\Proteus\Api\CategoriesApi;
use Ometra\Apollo\Proteus\Api\DirectoriesApi;

/**
 * Cliente principal para consumir la API de Proteus.
 *
 * @method array mediaIndex(array $data)
 * @method array mediaShow(string $id)
 * @method array uploadFile(array $data)
 * @method array|null mediaDelete(string $id)
 * @method array requestTransformations(string $id_media, array $data)
 * @method array mediaDownload(string $id, ?string $ext = null)
 * @method array saveMediaLocal(string $id, string $ext)
 * @method array metadataKeys(string $key)
 * @method array metadataValuesFromKey(string $key)
 * @method array metadataShow(string $id, string $key)
 * @method array metadataStore(string $id, array $data)
 * @method array metadataUpdate(string $id, array $data)
 * @method array|null metadataDelete(string $id, string $key)
 * @method array categoriesIndex()
 * @method array categoryStore(array $data)
 * @method array categoryUpdate(string $id, array $data)
 * @method array|null categoryDelete(string $id)
 * @method array categoryShow(string $id)
 * @method array directoriesIndex(array $data)
 * @method array directoryStore(array $data)
 * @method array directoryShow(string $id)
 * @method array|null directoryDelete(string $id)
 * @method array directoryUpdate(string $id, array $data)
 * @method array presetIndex(string $directory_id)
 * @method array presetStore(string $directory_id, array $data)
 * @method array|null presetDelete(string $directory_id, string $preset_id)
 * @method array presetShow(string $directory_id, string $preset_id)
 * @method array presetUpdate(string $directory_id, string $preset_id, array $data)
 */
class Proteus
{
    /**
     * @var array<int, object>
     */
    private array $scopes;

    private MediaApi $mediaApi;

    private MetadataApi $metadataApi;

    private CategoriesApi $categoriesApi;

    private DirectoriesApi $directoriesApi;

    private PresetsApi $presetsApi;

    public function __construct(
        ?MediaApi $mediaApi = null,
        ?MetadataApi $metadataApi = null,
        ?CategoriesApi $categoriesApi = null,
        ?DirectoriesApi $directoriesApi = null,
        ?PresetsApi $presetsApi = null,
    ) {
        $this->mediaApi = $mediaApi ?? app(MediaApi::class);
        $this->metadataApi = $metadataApi ?? app(MetadataApi::class);
        $this->categoriesApi = $categoriesApi ?? app(CategoriesApi::class);
        $this->directoriesApi = $directoriesApi ?? app(DirectoriesApi::class);
        $this->presetsApi = $presetsApi ?? app(PresetsApi::class);

        $this->scopes = [
            $this->mediaApi,
            $this->metadataApi,
            $this->categoriesApi,
            $this->directoriesApi,
            $this->presetsApi,
        ];
    }

    public function media(): MediaApi
    {
        return $this->mediaApi;
    }

    public function metadata(): MetadataApi
    {
        return $this->metadataApi;
    }

    public function categories(): CategoriesApi
    {
        return $this->categoriesApi;
    }

    public function directories(): DirectoriesApi
    {
        return $this->directoriesApi;
    }

    public function presets(): PresetsApi
    {
        return $this->presetsApi;
    }

    public function __call(string $method, array $arguments): mixed
    {
        foreach ($this->scopes as $scope) {
            if (method_exists($scope, $method)) {
                return $scope->{$method}(...$arguments);
            }
        }

        throw new BadMethodCallException(sprintf('Method [%s] does not exist on Proteus.', $method));
    }
}
