<?php

namespace Ometra\Apollo\Proteus\Api;

use Ometra\Caronte\Api\BaseApiClient;
use Ometra\Apollo\Proteus\Api\Concerns\ResolvesProteusTenant;

class MediaApi extends BaseApiClient
{
    use ResolvesProteusTenant;

    public function mediaIndex(array $data): array
    {
        return self::http()->applicationRequest('GET', 'media', query: $data, tenantId: $this->tenantId());
    }

    public function mediaShow(string $id): array
    {
        return self::http()->applicationRequest('GET', 'media/' . $id, tenantId: $this->tenantId());
    }

    public function uploadFile(array $data): array
    {
        return self::http()->applicationRequest('POST', 'media', payload: $data, tenantId: $this->tenantId());
    }

    public function mediaDelete(string $id): ?array
    {
        return self::http()->applicationRequest('DELETE', 'media/' . $id, tenantId: $this->tenantId());
    }

    public function requestTransformations(string $id_media, array $data): array
    {
        return self::http()->applicationRequest(
            'POST',
            'media/' . $id_media . '/request-transformations',
            payload: $data,
            tenantId: $this->tenantId()
        );
    }

    public function mediaDownload(string $id, ?string $ext = null): array
    {
        return self::http()->applicationRequest(
            'GET',
            'media/' . $id . '/download',
            query: array_filter(['ext' => $ext]),
            tenantId: $this->tenantId()
        );
    }

    public function saveMediaLocal(string $id, string $ext): array
    {
        return $this->mediaDownload($id, $ext);
    }
}
