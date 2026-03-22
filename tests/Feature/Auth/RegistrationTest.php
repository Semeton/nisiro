<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Models\Role;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post(route('register.store'), [
            'organization_name' => 'Acme Inc.',
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    }

    public function test_registration_creates_a_tenant_for_the_user(): void
    {
        $this->post(route('register.store'), [
            'organization_name' => 'Acme Inc.',
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tenants', ['name' => 'Acme Inc.']);

        /** @var User $user */
        $user = User::withoutGlobalScopes()->where('email', 'test@example.com')->firstOrFail();

        $this->assertNotNull($user->tenant_id);
        $this->assertDatabaseHas('tenants', ['id' => $user->tenant_id]);
    }

    public function test_registration_seeds_default_roles_for_the_tenant(): void
    {
        $this->post(route('register.store'), [
            'organization_name' => 'Acme Inc.',
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasNoErrors();

        /** @var User $user */
        $user = User::withoutGlobalScopes()->where('email', 'test@example.com')->firstOrFail();

        $this->assertDatabaseHas('roles', ['tenant_id' => $user->tenant_id, 'slug' => 'owner']);
        $this->assertDatabaseHas('roles', ['tenant_id' => $user->tenant_id, 'slug' => 'accountant']);
        $this->assertDatabaseHas('roles', ['tenant_id' => $user->tenant_id, 'slug' => 'staff']);
    }

    public function test_registration_assigns_owner_role_to_registering_user(): void
    {
        $this->post(route('register.store'), [
            'organization_name' => 'Acme Inc.',
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasNoErrors();

        /** @var User $user */
        $user = User::withoutGlobalScopes()->where('email', 'test@example.com')->firstOrFail();

        $ownerRole = Role::where('tenant_id', $user->tenant_id)->where('slug', 'owner')->firstOrFail();

        $this->assertDatabaseHas('model_has_roles', [
            'model_id' => $user->id,
            'role_id' => $ownerRole->id,
            'tenant_id' => $user->tenant_id,
        ]);
    }

    public function test_registration_requires_organization_name(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('organization_name');
    }
}
