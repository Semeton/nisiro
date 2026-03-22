<?php

declare(strict_types=1);

namespace Modules\Bookkeeping\Actions;

use Modules\Bookkeeping\Enums\AccountType;
use Modules\Bookkeeping\Models\AccountCategory;
use Modules\Bookkeeping\Models\Ledger;
use Modules\System\Models\Tenant;

class SeedDefaultChartOfAccountsAction
{
    /**
     * Seed a default chart of accounts for a new tenant.
     *
     * Returns a map of semantic keys to ledger UUIDs, suitable for
     * storing in tenant.settings['ledgers'] so other modules can
     * resolve accounts by role without hard-coded codes.
     *
     * @return array<string, string>
     */
    public function execute(Tenant $tenant): array
    {
        $assets = AccountCategory::create([
            'tenant_id' => $tenant->id,
            'name' => 'Assets',
            'type' => AccountType::Assets,
            'is_system' => true,
        ]);

        $liabilities = AccountCategory::create([
            'tenant_id' => $tenant->id,
            'name' => 'Liabilities',
            'type' => AccountType::Liabilities,
            'is_system' => true,
        ]);

        $equity = AccountCategory::create([
            'tenant_id' => $tenant->id,
            'name' => 'Equity',
            'type' => AccountType::Equity,
            'is_system' => true,
        ]);

        $revenue = AccountCategory::create([
            'tenant_id' => $tenant->id,
            'name' => 'Revenue',
            'type' => AccountType::Revenue,
            'is_system' => true,
        ]);

        $expense = AccountCategory::create([
            'tenant_id' => $tenant->id,
            'name' => 'Expenses',
            'type' => AccountType::Expense,
            'is_system' => true,
        ]);

        $cash = Ledger::create(['tenant_id' => $tenant->id, 'account_category_id' => $assets->id, 'code' => '1000', 'name' => 'Cash']);
        $bank = Ledger::create(['tenant_id' => $tenant->id, 'account_category_id' => $assets->id, 'code' => '1010', 'name' => 'Bank Account']);
        $ar = Ledger::create(['tenant_id' => $tenant->id, 'account_category_id' => $assets->id, 'code' => '1100', 'name' => 'Accounts Receivable']);
        $inventory = Ledger::create(['tenant_id' => $tenant->id, 'account_category_id' => $assets->id, 'code' => '1200', 'name' => 'Inventory']);
        $ap = Ledger::create(['tenant_id' => $tenant->id, 'account_category_id' => $liabilities->id, 'code' => '2000', 'name' => 'Accounts Payable']);
        $ownerEquity = Ledger::create(['tenant_id' => $tenant->id, 'account_category_id' => $equity->id, 'code' => '3000', 'name' => "Owner's Equity"]);
        $salesRevenue = Ledger::create(['tenant_id' => $tenant->id, 'account_category_id' => $revenue->id, 'code' => '4000', 'name' => 'Sales Revenue']);
        $cogs = Ledger::create(['tenant_id' => $tenant->id, 'account_category_id' => $expense->id, 'code' => '5000', 'name' => 'Cost of Goods Sold']);
        $opex = Ledger::create(['tenant_id' => $tenant->id, 'account_category_id' => $expense->id, 'code' => '5100', 'name' => 'Operating Expenses']);

        return [
            'cash' => $cash->id,
            'bank' => $bank->id,
            'accounts_receivable' => $ar->id,
            'inventory' => $inventory->id,
            'accounts_payable' => $ap->id,
            'owner_equity' => $ownerEquity->id,
            'sales_revenue' => $salesRevenue->id,
            'cogs' => $cogs->id,
            'operating_expenses' => $opex->id,
        ];
    }
}
