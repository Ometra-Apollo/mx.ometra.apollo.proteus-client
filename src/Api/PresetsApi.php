<?php

namespace Ometra\Apollo\Proteus\Api;

use Ometra\Caronte\Api\BaseApiClient;
use Ometra\Apollo\Proteus\Api\Concerns\ResolvesProteusTenant;

class PresetsApi extends BaseApiClient
{
    use ResolvesProteusTenant;

    public function presetIndex(string $directory_id): array
    {
        return self::http()->applicationRequest('GET', 'directories/' . $directory_id . '/presets', tenantId: $this->tenantId());
    }

    public function presetStore(string $directory_id, array $data): array
    {
        return self::http()->applicationRequest(
            'POST',
            'directories/' . $directory_id . '/presets',
            payload: $data,
            tenantId: $this->tenantId()
        );
    }

    public function presetDelete(string $directory_id, string $preset_id): ?array
    {
        return self::http()->applicationRequest(
            'DELETE',
            'directories/' . $directory_id . '/presets/' . $preset_id,
            tenantId: $this->tenantId()
        );
    }

    public function presetShow(string $directory_id, string $preset_id): array
    {
        return self::http()->applicationRequest(
            'GET',
            'directories/' . $directory_id . '/presets/' . $preset_id,
            tenantId: $this->tenantId()
        );
    }

    public function presetUpdate(string $directory_id, string $preset_id, array $data): array
    {
        return self::http()->applicationRequest(
            'PUT',
            'directories/' . $directory_id . '/presets/' . $preset_id,
            payload: $data,
            tenantId: $this->tenantId()
        );
    }
}
