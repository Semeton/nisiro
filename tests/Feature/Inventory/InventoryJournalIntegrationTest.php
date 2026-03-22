<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Bookkeeping\Services\AccountBalanceService;
use Modules\Inventory\Actions\AdjustStockAction;
use Modules\Inventory\Actions\PurchaseStockAction;
use Modules\Inventory\Actions\SellStockAction;
use Tests\Concerns\InteractsWithBookkeeping;
use Tests\Concerns\InteractsWithInventory;
use Tests\TestCase;

class InventoryJournalIntegrationTest extends TestCase
{
    use InteractsWithBookkeeping, InteractsWithInventory, RefreshDatabase;

    public function test_purchasing_stock_posts_debit_to_inventory_ledger(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $item = $this->createStockItem($tenant, 'Rice Bag', 'RICE-50KG');

        $this->app->make(PurchaseStockAction::class)->execute($item, 10, 50.00);

        // 10 units × ₦50 = ₦500 debited to Inventory
        $this->assertDatabaseHas('entry_lines', [
            'type' => 'debit',
            'amount' => '500.00',
            'ledger_id' => $tenant->settings['ledgers']['inventory'],
        ]);
    }

    public function test_purchasing_stock_posts_credit_to_accounts_payable(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $item = $this->createStockItem($tenant, 'Rice Bag', 'RICE-50KG');

        $this->app->make(PurchaseStockAction::class)->execute($item, 10, 50.00);

        $this->assertDatabaseHas('entry_lines', [
            'type' => 'credit',
            'amount' => '500.00',
            'ledger_id' => $tenant->settings['ledgers']['accounts_payable'],
        ]);
    }

    public function test_selling_stock_posts_debit_to_cogs(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $item = $this->createStockItem($tenant, 'Rice Bag', 'RICE-50KG');

        $this->app->make(PurchaseStockAction::class)->execute($item, 10, 50.00);
        $this->app->make(SellStockAction::class)->execute($item->fresh(), 4);

        // 4 units × ₦50 cost = ₦200 debited to COGS
        $this->assertDatabaseHas('entry_lines', [
            'type' => 'debit',
            'amount' => '200.00',
            'ledger_id' => $tenant->settings['ledgers']['cogs'],
        ]);
    }

    public function test_selling_stock_posts_credit_to_inventory_ledger(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $item = $this->createStockItem($tenant, 'Rice Bag', 'RICE-50KG');

        $this->app->make(PurchaseStockAction::class)->execute($item, 10, 50.00);
        $this->app->make(SellStockAction::class)->execute($item->fresh(), 4);

        $this->assertDatabaseHas('entry_lines', [
            'type' => 'credit',
            'amount' => '200.00',
            'ledger_id' => $tenant->settings['ledgers']['inventory'],
        ]);
    }

    public function test_inventory_ledger_balance_reflects_net_purchases_minus_sales(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $item = $this->createStockItem($tenant, 'Rice Bag', 'RICE-50KG');

        $purchase = $this->app->make(PurchaseStockAction::class);
        $sell = $this->app->make(SellStockAction::class);

        $purchase->execute($item, 10, 50.00); // +500 to Inventory
        $sell->execute($item->fresh(), 3);     // -150 from Inventory

        $inventoryLedger = \Modules\Bookkeeping\Models\Ledger::find($tenant->settings['ledgers']['inventory'])
            ->load('accountCategory');

        $balance = $this->app->make(AccountBalanceService::class)->balanceForLedger($inventoryLedger);

        $this->assertEqualsWithDelta(350.00, $balance, 0.001);
    }

    public function test_negative_adjustment_posts_debit_to_operating_expenses(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $item = $this->createStockItem($tenant, 'Widget', 'WGT-001');

        $this->app->make(PurchaseStockAction::class)->execute($item, 20, 10.00);
        $this->app->make(AdjustStockAction::class)->execute($item->fresh(), -2, 'Damaged goods');

        // Loss: 2 units × ₦10 = ₦20 debited to Operating Expenses
        $this->assertDatabaseHas('entry_lines', [
            'type' => 'debit',
            'amount' => '20.00',
            'ledger_id' => $tenant->settings['ledgers']['operating_expenses'],
        ]);
    }

    public function test_journal_entries_are_isolated_between_tenants(): void
    {
        $tenantA = $this->createTenantWithChartOfAccounts(['name' => 'Tenant A']);
        $itemA = $this->createStockItem($tenantA, 'Widget', 'WGT-001');
        $this->app->make(PurchaseStockAction::class)->execute($itemA, 5, 100.00);

        // Tenant B has no stock activity.
        $tenantB = $this->createTenantWithChartOfAccounts(['name' => 'Tenant B']);
        $this->forTenant($tenantB);

        $inventoryLedgerB = \Modules\Bookkeeping\Models\Ledger::find($tenantB->settings['ledgers']['inventory'])
            ->load('accountCategory');

        $balance = $this->app->make(AccountBalanceService::class)->balanceForLedger($inventoryLedgerB);

        $this->assertEqualsWithDelta(0.00, $balance, 0.001);
    }
}
