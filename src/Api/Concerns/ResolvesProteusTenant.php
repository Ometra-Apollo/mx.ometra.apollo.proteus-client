<?php

namespace Ometra\Apollo\Proteus\Api\Concerns;

use Equidna\BeeHive\Tenancy\TenantContext;
use RuntimeException;

trait ResolvesProteusTenant
{
    protected function tenantId(): string
    {
        /** @var TenantContext $context */
        $context  = app(TenantContext::class);
        $tenantId = $context->get();

        if ($tenantId === null) {
            throw new RuntimeException('Tenant ID not found in Bee Hive TenantContext.');
        }

        return (string) $tenantId;
    }
}
