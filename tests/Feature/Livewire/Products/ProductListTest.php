<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Products;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\Concerns\InteractsWithInventory;
use Tests\TestCase;

class ProductListTest extends TestCase
{
    use InteractsWithInventory, RefreshDatabase;

    public function test_products_page_renders(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get(route('products.index'))
            ->assertOk();
    }

    public function test_products_page_shows_empty_state_when_no_products(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        Volt::actingAs($user)
            ->test('products.index')
            ->assertSee('No products yet')
            ->assertOk();
    }

    public function test_products_page_lists_products(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->createStockItem($tenant, 'Rice Bag', 'RICE-50KG');

        Volt::actingAs($user)
            ->test('products.index')
            ->assertSee('Rice Bag')
            ->assertSee('RICE-50KG')
            ->assertOk();
    }

    public function test_products_page_filters_by_search(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->createStockItem($tenant, 'Rice Bag', 'RICE-50KG');
        $this->createStockItem($tenant, 'Sugar Pack', 'SGR-1KG');

        Volt::actingAs($user)
            ->test('products.index')
            ->set('search', 'Rice')
            ->assertSee('Rice Bag')
            ->assertDontSee('Sugar Pack')
            ->assertOk();
    }
}
