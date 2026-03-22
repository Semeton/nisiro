<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Products;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithInventory;
use Tests\TestCase;

class ProductDetailTest extends TestCase
{
    use InteractsWithInventory, RefreshDatabase;

    public function test_product_detail_page_renders(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $item = $this->createStockItem($tenant, 'Widget', 'WGT-001', 10.00, 20.00);

        $this->actingAs($user)
            ->get(route('products.show', $item))
            ->assertOk()
            ->assertSee('Widget')
            ->assertSee('WGT-001');
    }

    public function test_product_detail_shows_stock_info(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $item = $this->createStockItem($tenant, 'Widget', 'WGT-001', 10.00, 20.00);

        $this->actingAs($user)
            ->get(route('products.show', $item))
            ->assertSee('In Stock')
            ->assertSee('Cost Price')
            ->assertSee('Selling Price');
    }
}
