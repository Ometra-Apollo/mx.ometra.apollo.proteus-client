<?php

namespace Ometra\Apollo\Proteus\Api;

use Ometra\Caronte\Api\BaseApiClient;
use Ometra\Apollo\Proteus\Api\Concerns\ResolvesProteusTenant;

class MetadataApi extends BaseApiClient
{
    use ResolvesProteusTenant;

    public function metadataKeys(string $key): array
    {
        return self::http()->applicationRequest('GET', 'media/metadata/' . $key, tenantId: $this->tenantId());
    }

    public function metadataValuesFromKey(string $key): array
    {
        return self::http()->applicationRequest('GET', 'media/metadata/' . $key . '/values', tenantId: $this->tenantId());
    }

    public function metadataShow(string $id, string $key): array
    {
        return self::http()->applicationRequest('GET', 'media/' . $id . '/metadata/' . $key, tenantId: $this->tenantId());
    }

    public function metadataStore(string $id, array $data): array
    {
        return self::http()->applicationRequest('POST', 'media/' . $id . '/metadata', payload: $data, tenantId: $this->tenantId());
    }

    public function metadataUpdate(string $id, array $data): array
    {
        return self::http()->applicationRequest('PUT', 'media/' . $id . '/metadata', payload: $data, tenantId: $this->tenantId());
    }

    public function metadataDelete(string $id, string $key): ?array
    {
        return self::http()->applicationRequest('DELETE', 'media/' . $id . '/metadata/' . $key, tenantId: $this->tenantId());
    }
}
