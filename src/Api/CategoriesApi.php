<?php

namespace Ometra\Apollo\Proteus\Api;

use Ometra\Caronte\Api\BaseApiClient;
use Ometra\Apollo\Proteus\Api\Concerns\ResolvesProteusTenant;

class CategoriesApi extends BaseApiClient
{
    use ResolvesProteusTenant;

    public function categoriesIndex(): array
    {
        return self::http()->applicationRequest('GET', 'categories', tenantId: $this->tenantId());
    }

    public function categoryStore(array $data): array
    {
        return self::http()->applicationRequest('POST', 'categories', payload: $data, tenantId: $this->tenantId());
    }

    public function categoryUpdate(string $id, array $data): array
    {
        return self::http()->applicationRequest('PUT', 'categories/' . $id, payload: $data, tenantId: $this->tenantId());
    }

    public function categoryDelete(string $id): ?array
    {
        return self::http()->applicationRequest('DELETE', 'categories/' . $id, tenantId: $this->tenantId());
    }

    public function categoryShow(string $id): array
    {
        return self::http()->applicationRequest('GET', 'categories/' . $id, tenantId: $this->tenantId());
    }
}
