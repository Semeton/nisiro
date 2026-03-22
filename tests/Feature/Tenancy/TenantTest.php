<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_tenant(): void
    {
        $tenant = $this->createTenant([
            'name' => 'Test Tenant',
            'settings' => ['currency' => 'USD'],
        ]);

        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
            'name' => 'Test Tenant',
        ]);
    }

    public function test_resolves_tenant_from_header(): void
    {
        $tenant = $this->createTenant([
            'name' => 'Header Tenant',
            'domain' => 'header-tenant.test',
            'subdomain' => 'header-tenant',
        ]);

        $this->get(route('home'), [
            'X-Tenant-ID' => $tenant->domain,
        ])->assertOk();

        $this->assertTrue(app()->has('currentTenant'));
        $this->assertSame($tenant->id, app('currentTenant')->id);
    }

    public function test_for_tenant_helper_sets_current_tenant(): void
    {
        $tenant = $this->createTenant([
            'name' => 'Helper Tenant',
        ]);

        $this->forTenant($tenant);

        $this->assertTrue(app()->has('currentTenant'));
        $this->assertSame($tenant->id, app('currentTenant')->id);
    }
}
