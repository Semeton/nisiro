<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\Concerns\InteractsWithInventory;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use InteractsWithInventory, RefreshDatabase;

    public function test_dashboard_renders_for_authenticated_user_with_tenant(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_dashboard_shows_summary_cards(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        Volt::actingAs($user)
            ->test('dashboard')
            ->assertSee('Cash on Hand')
            ->assertSee('Bank Balance')
            ->assertSee('Total Stock Value')
            ->assertOk();
    }

    public function test_dashboard_shows_quick_action_buttons(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        Volt::actingAs($user)
            ->test('dashboard')
            ->assertSee('Add Product')
            ->assertSee('Record Purchase')
            ->assertSee('Record Sale')
            ->assertOk();
    }
}
