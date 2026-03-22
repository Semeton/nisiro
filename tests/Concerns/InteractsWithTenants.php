<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Modules\System\Models\Tenant;

trait InteractsWithTenants
{
    protected function createTenant(array $attributes = []): Tenant
    {
        $defaults = [
            'name' => 'Test Tenant',
            'domain' => 'tenant-'.uniqid('', true).'.test',
            'subdomain' => 'tenant-'.uniqid('', true),
            'settings' => [],
        ];

        return Tenant::create(array_merge($defaults, $attributes));
    }

    protected function forTenant(Tenant $tenant): void
    {
        app()->instance('currentTenant', $tenant);
    }
}
