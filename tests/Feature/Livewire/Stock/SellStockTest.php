<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Stock;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Modules\Inventory\Actions\PurchaseStockAction;
use Tests\Concerns\InteractsWithInventory;
use Tests\TestCase;

class SellStockTest extends TestCase
{
    use InteractsWithInventory, RefreshDatabase;

    public function test_sell_page_renders(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get(route('stock.sell'))
            ->assertOk();
    }

    public function test_sale_records_stock_out_and_redirects(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $item = $this->createStockItem($tenant, 'Widget', 'WGT-001');
        app(PurchaseStockAction::class)->execute($item, 20, 10.00);

        Volt::actingAs($user)
            ->test('stock.sell')
            ->set('stock_item_id', $item->id)
            ->set('quantity', '5')
            ->call('sell')
            ->assertHasNoErrors()
            ->assertRedirect(route('products.show', $item));

        $this->assertDatabaseHas('stock_items', [
            'id' => $item->id,
            'quantity_on_hand' => '15.00',
        ]);
    }

    public function test_selling_more_than_available_shows_error(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $item = $this->createStockItem($tenant, 'Widget', 'WGT-001');
        app(PurchaseStockAction::class)->execute($item, 5, 10.00);

        Volt::actingAs($user)
            ->test('stock.sell')
            ->set('stock_item_id', $item->id)
            ->set('quantity', '10')
            ->call('sell')
            ->assertHasErrors('quantity');
    }
}
