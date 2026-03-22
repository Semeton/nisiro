<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Inventory\Actions\AdjustStockAction;
use Modules\Inventory\Actions\PurchaseStockAction;
use Modules\Inventory\Actions\SellStockAction;
use Modules\Inventory\Enums\MovementType;
use Modules\Inventory\Exceptions\InsufficientStockException;
use Modules\Inventory\Services\StockQuantityService;
use Tests\Concerns\InteractsWithInventory;
use Tests\TestCase;

class StockManagementTest extends TestCase
{
    use InteractsWithInventory, RefreshDatabase;

    public function test_purchasing_stock_increases_quantity_on_hand(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $item = $this->createStockItem($tenant, 'Widget', 'WGT-001');

        $this->app->make(PurchaseStockAction::class)->execute($item, 50, 10.00);

        $this->assertDatabaseHas('stock_items', ['id' => $item->id, 'quantity_on_hand' => 50]);
    }

    public function test_selling_stock_decreases_quantity_on_hand(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $item = $this->createStockItem($tenant, 'Widget', 'WGT-001');

        $purchase = $this->app->make(PurchaseStockAction::class);
        $sell = $this->app->make(SellStockAction::class);

        $purchase->execute($item, 50, 10.00);
        $sell->execute($item->fresh(), 20);

        $this->assertDatabaseHas('stock_items', ['id' => $item->id, 'quantity_on_hand' => 30]);
    }

    public function test_selling_more_than_available_throws_exception(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $item = $this->createStockItem($tenant, 'Widget', 'WGT-001');

        $this->app->make(PurchaseStockAction::class)->execute($item, 10, 10.00);

        $this->expectException(InsufficientStockException::class);

        $this->app->make(SellStockAction::class)->execute($item->fresh(), 20);
    }

    public function test_purchase_creates_stock_batch(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $item = $this->createStockItem($tenant, 'Widget', 'WGT-001');

        $this->app->make(PurchaseStockAction::class)->execute($item, 30, 15.00);

        $this->assertDatabaseHas('stock_batches', [
            'stock_item_id' => $item->id,
            'quantity' => 30,
            'quantity_remaining' => 30,
            'unit_cost' => '15.00',
        ]);
    }

    public function test_purchase_creates_stock_movement_record(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $item = $this->createStockItem($tenant, 'Widget', 'WGT-001');

        $this->app->make(PurchaseStockAction::class)->execute($item, 10, 5.00);

        $this->assertDatabaseHas('stock_movements', [
            'stock_item_id' => $item->id,
            'type' => MovementType::Purchase->value,
            'quantity' => '10.00',
            'unit_cost' => '5.00',
        ]);
    }

    public function test_sale_creates_negative_stock_movement(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $item = $this->createStockItem($tenant, 'Widget', 'WGT-001');

        $purchase = $this->app->make(PurchaseStockAction::class);
        $sell = $this->app->make(SellStockAction::class);

        $purchase->execute($item, 20, 10.00);
        $sell->execute($item->fresh(), 5);

        $this->assertDatabaseHas('stock_movements', [
            'stock_item_id' => $item->id,
            'type' => MovementType::Sale->value,
            'quantity' => '-5.00',
        ]);
    }

    public function test_purchasing_updates_weighted_average_cost(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $item = $this->createStockItem($tenant, 'Widget', 'WGT-001');

        $purchase = $this->app->make(PurchaseStockAction::class);

        $purchase->execute($item, 10, 10.00); // total cost 100
        $purchase->execute($item->fresh(), 10, 20.00); // total cost 200

        // Weighted avg = (100 + 200) / 20 = 15.00
        $this->assertDatabaseHas('stock_items', ['id' => $item->id, 'cost_price' => '15.00']);
    }

    public function test_adjustment_increases_stock_when_quantity_is_positive(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $item = $this->createStockItem($tenant, 'Widget', 'WGT-001', 10.00);

        $this->app->make(AdjustStockAction::class)->execute($item, 5);

        $this->assertDatabaseHas('stock_items', ['id' => $item->id, 'quantity_on_hand' => 5]);
    }

    public function test_adjustment_decreases_stock_when_quantity_is_negative(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $item = $this->createStockItem($tenant, 'Widget', 'WGT-001');

        $this->app->make(PurchaseStockAction::class)->execute($item, 20, 10.00);
        $this->app->make(AdjustStockAction::class)->execute($item->fresh(), -3);

        $this->assertDatabaseHas('stock_items', ['id' => $item->id, 'quantity_on_hand' => 17]);
    }

    public function test_stock_quantity_service_matches_model_value(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $item = $this->createStockItem($tenant, 'Widget', 'WGT-001');

        $this->app->make(PurchaseStockAction::class)->execute($item, 40, 10.00);
        $this->app->make(SellStockAction::class)->execute($item->fresh(), 15);

        $fresh = $item->fresh();
        $service = $this->app->make(StockQuantityService::class);

        $this->assertEqualsWithDelta(25.0, $service->quantityOnHand($fresh), 0.001);
        $this->assertEqualsWithDelta(25.0, $service->recomputeFromMovements($fresh), 0.001);
    }
}
