<?php

namespace Ometra\Apollo\Proteus\Api;

use Ometra\Caronte\Api\BaseApiClient;
use Ometra\Apollo\Proteus\Api\Concerns\ResolvesProteusTenant;

class DirectoriesApi extends BaseApiClient
{
    use ResolvesProteusTenant;

    public function directoriesIndex(array $data): array
    {
        return self::http()->applicationRequest('GET', 'directories', query: $data, tenantId: $this->tenantId());
    }

    public function directoryStore(array $data): array
    {
        return self::http()->applicationRequest('POST', 'directories', payload: $data, tenantId: $this->tenantId());
    }

    public function directoryShow(string $id): array
    {
        return self::http()->applicationRequest('GET', 'directories/' . $id, tenantId: $this->tenantId());
    }

    public function directoryDelete(string $id): ?array
    {
        return self::http()->applicationRequest('DELETE', 'directories/' . $id, tenantId: $this->tenantId());
    }

    public function directoryUpdate(string $id, array $data): array
    {
        return self::http()->applicationRequest('PUT', 'directories/' . $id, payload: $data, tenantId: $this->tenantId());
    }
}
