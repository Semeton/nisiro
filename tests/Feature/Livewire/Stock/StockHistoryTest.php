<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Stock;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Modules\Inventory\Actions\PurchaseStockAction;
use Tests\Concerns\InteractsWithInventory;
use Tests\TestCase;

class StockHistoryTest extends TestCase
{
    use InteractsWithInventory, RefreshDatabase;

    public function test_history_page_renders(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get(route('stock.history'))
            ->assertOk();
    }

    public function test_history_shows_movements(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $item = $this->createStockItem($tenant, 'Rice Bag', 'RICE-50KG');
        app(PurchaseStockAction::class)->execute($item, 10, 50.00);

        Volt::actingAs($user)
            ->test('stock.history')
            ->assertSee('Rice Bag')
            ->assertSee('Purchase')
            ->assertOk();
    }

    public function test_history_filters_by_type(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $item = $this->createStockItem($tenant, 'Rice Bag', 'RICE-50KG');
        app(PurchaseStockAction::class)->execute($item, 10, 50.00);

        Volt::actingAs($user)
            ->test('stock.history')
            ->assertSee('Purchase')
            ->set('typeFilter', 'sale')
            ->assertSee('No stock movements found')
            ->assertOk();
    }
}
