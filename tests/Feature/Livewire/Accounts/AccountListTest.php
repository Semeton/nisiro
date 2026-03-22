<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Accounts;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\Concerns\InteractsWithInventory;
use Tests\TestCase;

class AccountListTest extends TestCase
{
    use InteractsWithInventory, RefreshDatabase;

    public function test_accounts_page_renders(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get(route('accounts.index'))
            ->assertOk();
    }

    public function test_accounts_page_shows_default_chart_of_accounts(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        Volt::actingAs($user)
            ->test('accounts.index')
            ->assertSee('Cash')
            ->assertSee('Bank Account')
            ->assertSee('Inventory')
            ->assertSee('Accounts Payable')
            ->assertSee('What You Own')
            ->assertSee('What You Owe')
            ->assertOk();
    }
}
