<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Stock;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Modules\Inventory\Actions\PurchaseStockAction;
use Tests\Concerns\InteractsWithInventory;
use Tests\TestCase;

class AdjustStockTest extends TestCase
{
    use InteractsWithInventory, RefreshDatabase;

    public function test_adjust_page_renders(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get(route('stock.adjust'))
            ->assertOk();
    }

    public function test_adjustment_removes_stock(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $item = $this->createStockItem($tenant, 'Widget', 'WGT-001');
        app(PurchaseStockAction::class)->execute($item, 20, 10.00);

        Volt::actingAs($user)
            ->test('stock.adjust')
            ->set('stock_item_id', $item->id)
            ->set('direction', 'remove')
            ->set('quantity', '3')
            ->set('notes', 'Damaged goods')
            ->call('adjust')
            ->assertHasNoErrors()
            ->assertRedirect(route('products.show', $item));

        $this->assertDatabaseHas('stock_items', [
            'id' => $item->id,
            'quantity_on_hand' => '17.00',
        ]);
    }

    public function test_adjustment_requires_reason(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $item = $this->createStockItem($tenant, 'Widget', 'WGT-001');

        Volt::actingAs($user)
            ->test('stock.adjust')
            ->set('stock_item_id', $item->id)
            ->set('direction', 'remove')
            ->set('quantity', '3')
            ->set('notes', '')
            ->call('adjust')
            ->assertHasErrors(['notes' => 'required']);
    }
}
