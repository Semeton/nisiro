<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Products;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\Concerns\InteractsWithInventory;
use Tests\TestCase;

class CreateProductTest extends TestCase
{
    use InteractsWithInventory, RefreshDatabase;

    public function test_create_product_page_renders(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get(route('products.create'))
            ->assertOk();
    }

    public function test_product_can_be_created(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        Volt::actingAs($user)
            ->test('products.create')
            ->set('name', 'Rice Bag')
            ->set('sku', 'RICE-50KG')
            ->set('cost_price', '500')
            ->set('selling_price', '750')
            ->set('reorder_level', '10')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('stock_items', [
            'tenant_id' => $tenant->id,
            'name' => 'Rice Bag',
            'sku' => 'RICE-50KG',
        ]);
    }

    public function test_product_name_is_required(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        Volt::actingAs($user)
            ->test('products.create')
            ->set('name', '')
            ->set('sku', 'TEST')
            ->set('cost_price', '100')
            ->set('selling_price', '200')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_sku_must_be_unique(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->createStockItem($tenant, 'Existing', 'DUP-SKU');

        Volt::actingAs($user)
            ->test('products.create')
            ->set('name', 'New Product')
            ->set('sku', 'DUP-SKU')
            ->set('cost_price', '100')
            ->set('selling_price', '200')
            ->call('save')
            ->assertHasErrors(['sku' => 'unique']);
    }
}
