<?php

declare(strict_types=1);

namespace Tests\Feature\Identity;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Models\Permission;
use Modules\Identity\Models\Role;
use Modules\Identity\Services\PermissionService;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_assigned_role_and_permission_is_inferred(): void
    {
        $tenant = $this->createTenant();
        $this->forTenant($tenant);

        /** @var User $user */
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $role = Role::create([
            'tenant_id' => $tenant->id,
            'name' => 'Owner',
            'slug' => 'owner',
        ]);

        $permission = Permission::create([
            'tenant_id' => $tenant->id,
            'name' => 'View Dashboard',
            'slug' => 'view-dashboard',
        ]);

        $role->permissions()->attach($permission->id, [
            'tenant_id' => $tenant->id,
        ]);

        $user->roles()->attach($role->id, [
            'tenant_id' => $tenant->id,
        ]);

        $service = $this->app->make(PermissionService::class);

        $this->assertTrue($service->userHasPermission($user, 'view-dashboard'));
    }

    public function test_permissions_are_isolated_by_tenant(): void
    {
        $tenantA = $this->createTenant(['name' => 'Tenant A']);
        $tenantB = $this->createTenant(['name' => 'Tenant B']);

        /** @var User $userA */
        $userA = User::factory()->create([
            'tenant_id' => $tenantA->id,
        ]);

        /** @var User $userB */
        $userB = User::factory()->create([
            'tenant_id' => $tenantB->id,
        ]);

        $roleA = Role::create([
            'tenant_id' => $tenantA->id,
            'name' => 'Owner',
            'slug' => 'owner',
        ]);

        $permission = Permission::create([
            'tenant_id' => $tenantA->id,
            'name' => 'Manage Books',
            'slug' => 'manage-books',
        ]);

        $roleA->permissions()->attach($permission->id, [
            'tenant_id' => $tenantA->id,
        ]);

        $userA->roles()->attach($roleA->id, [
            'tenant_id' => $tenantA->id,
        ]);

        $service = $this->app->make(PermissionService::class);

        $this->assertTrue($service->userHasPermission($userA, 'manage-books'));
        $this->assertFalse($service->userHasPermission($userB, 'manage-books'));
    }
}
