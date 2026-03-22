<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Stock;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\Concerns\InteractsWithInventory;
use Tests\TestCase;

class PurchaseStockTest extends TestCase
{
    use InteractsWithInventory, RefreshDatabase;

    public function test_purchase_page_renders(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get(route('stock.purchase'))
            ->assertOk();
    }

    public function test_purchase_records_stock_and_redirects(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $item = $this->createStockItem($tenant, 'Widget', 'WGT-001');

        Volt::actingAs($user)
            ->test('stock.purchase')
            ->set('stock_item_id', $item->id)
            ->set('quantity', '10')
            ->set('unit_cost', '500')
            ->call('purchase')
            ->assertHasNoErrors()
            ->assertRedirect(route('products.show', $item));

        $this->assertDatabaseHas('stock_items', [
            'id' => $item->id,
            'quantity_on_hand' => '10.00',
        ]);
    }

    public function test_purchase_requires_product_selection(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        Volt::actingAs($user)
            ->test('stock.purchase')
            ->set('quantity', '10')
            ->set('unit_cost', '500')
            ->call('purchase')
            ->assertHasErrors(['stock_item_id' => 'required']);
    }
}
