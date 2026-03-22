<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Modules\Bookkeeping\Actions\SeedDefaultChartOfAccountsAction;
use Modules\Inventory\Models\StockItem;
use Modules\System\Models\Tenant;

trait InteractsWithInventory
{
    /**
     * Create a tenant with a full default chart of accounts seeded into settings.
     * This mirrors what CreateNewUser does at registration.
     */
    protected function createTenantWithChartOfAccounts(array $attributes = []): Tenant
    {
        $tenant = $this->createTenant($attributes);
        $this->forTenant($tenant);

        $ledgerMap = app(SeedDefaultChartOfAccountsAction::class)->execute($tenant);
        $tenant->update(['settings' => ['ledgers' => $ledgerMap]]);

        return $tenant->fresh();
    }

    protected function createStockItem(Tenant $tenant, string $name, string $sku, float $costPrice = 0.0, float $sellingPrice = 0.0): StockItem
    {
        return StockItem::create([
            'tenant_id' => $tenant->id,
            'name' => $name,
            'sku' => $sku,
            'cost_price' => $costPrice,
            'selling_price' => $sellingPrice,
            'quantity_on_hand' => 0,
        ]);
    }
}
