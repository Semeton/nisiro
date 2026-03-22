<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Transactions;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Modules\Inventory\Actions\PurchaseStockAction;
use Tests\Concerns\InteractsWithInventory;
use Tests\TestCase;

class TransactionListTest extends TestCase
{
    use InteractsWithInventory, RefreshDatabase;

    public function test_transactions_page_renders(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get(route('transactions.index'))
            ->assertOk();
    }

    public function test_transactions_page_shows_entries(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $item = $this->createStockItem($tenant, 'Widget', 'WGT-001');
        app(PurchaseStockAction::class)->execute($item, 10, 50.00);

        Volt::actingAs($user)
            ->test('transactions.index')
            ->assertSee('Widget')
            ->assertSee('Posted')
            ->assertOk();
    }

    public function test_transactions_page_shows_empty_state(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        Volt::actingAs($user)
            ->test('transactions.index')
            ->assertSee('No transactions yet')
            ->assertOk();
    }
}
