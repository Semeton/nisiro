<?php

declare(strict_types=1);

namespace Tests\Feature\Bookkeeping;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Bookkeeping\Actions\PostTransactionAction;
use Modules\Bookkeeping\Enums\AccountType;
use Modules\Bookkeeping\Services\AccountBalanceService;
use Tests\Concerns\InteractsWithBookkeeping;
use Tests\TestCase;

class AccountBalanceTest extends TestCase
{
    use InteractsWithBookkeeping, RefreshDatabase;

    public function test_asset_ledger_balance_increases_with_debits(): void
    {
        $tenant = $this->createTenant();
        $this->forTenant($tenant);

        $assets = $this->createAccountCategory($tenant, 'Assets', AccountType::Assets);
        $revenue = $this->createAccountCategory($tenant, 'Revenue', AccountType::Revenue);
        $cash = $this->createLedger($tenant, $assets, 'Cash', '1000');
        $salesRevenue = $this->createLedger($tenant, $revenue, 'Sales Revenue', '4000');

        $action = $this->app->make(PostTransactionAction::class);

        $action->execute('2026-03-15', 'Sale 1', [
            $this->debit($cash, 300.00),
            $this->credit($salesRevenue, 300.00),
        ]);

        $action->execute('2026-03-15', 'Sale 2', [
            $this->debit($cash, 200.00),
            $this->credit($salesRevenue, 200.00),
        ]);

        $service = $this->app->make(AccountBalanceService::class);

        $this->assertEqualsWithDelta(500.00, $service->balanceForLedger($cash->fresh(['accountCategory'])), 0.001);
    }

    public function test_revenue_ledger_balance_increases_with_credits(): void
    {
        $tenant = $this->createTenant();
        $this->forTenant($tenant);

        $assets = $this->createAccountCategory($tenant, 'Assets', AccountType::Assets);
        $revenue = $this->createAccountCategory($tenant, 'Revenue', AccountType::Revenue);
        $cash = $this->createLedger($tenant, $assets, 'Cash', '1000');
        $salesRevenue = $this->createLedger($tenant, $revenue, 'Sales Revenue', '4000');

        $action = $this->app->make(PostTransactionAction::class);

        $action->execute('2026-03-15', 'Sale', [
            $this->debit($cash, 750.00),
            $this->credit($salesRevenue, 750.00),
        ]);

        $service = $this->app->make(AccountBalanceService::class);

        $this->assertEqualsWithDelta(750.00, $service->balanceForLedger($salesRevenue->fresh(['accountCategory'])), 0.001);
    }

    public function test_balance_only_counts_posted_entries(): void
    {
        $tenant = $this->createTenant();
        $this->forTenant($tenant);

        $assets = $this->createAccountCategory($tenant, 'Assets', AccountType::Assets);
        $revenue = $this->createAccountCategory($tenant, 'Revenue', AccountType::Revenue);
        $cash = $this->createLedger($tenant, $assets, 'Cash', '1000');
        $salesRevenue = $this->createLedger($tenant, $revenue, 'Sales Revenue', '4000');

        // A draft entry (posted_at = null) — should not affect balance.
        $draftEntry = \Modules\Bookkeeping\Models\Entry::create([
            'tenant_id' => $tenant->id,
            'date' => '2026-03-15',
            'description' => 'Draft',
            'posted_at' => null,
        ]);

        $draftEntry->lines()->create([
            'tenant_id' => $tenant->id,
            'ledger_id' => $cash->id,
            'type' => \Modules\Bookkeeping\Enums\LineType::Debit,
            'amount' => 999.00,
        ]);

        $service = $this->app->make(AccountBalanceService::class);

        $this->assertEqualsWithDelta(0.00, $service->balanceForLedger($cash->fresh(['accountCategory'])), 0.001);
    }

    public function test_balances_are_isolated_between_tenants(): void
    {
        $tenantA = $this->createTenant(['name' => 'Tenant A']);
        $tenantB = $this->createTenant(['name' => 'Tenant B']);

        $this->forTenant($tenantA);
        $assetsA = $this->createAccountCategory($tenantA, 'Assets', AccountType::Assets);
        $revenueA = $this->createAccountCategory($tenantA, 'Revenue', AccountType::Revenue);
        $cashA = $this->createLedger($tenantA, $assetsA, 'Cash', '1000');
        $salesA = $this->createLedger($tenantA, $revenueA, 'Sales', '4000');

        $action = $this->app->make(PostTransactionAction::class);
        $action->execute('2026-03-15', 'Tenant A sale', [
            $this->debit($cashA, 400.00),
            $this->credit($salesA, 400.00),
        ]);

        $this->forTenant($tenantB);
        $assetsB = $this->createAccountCategory($tenantB, 'Assets', AccountType::Assets);
        $revenueB = $this->createAccountCategory($tenantB, 'Revenue', AccountType::Revenue);
        $cashB = $this->createLedger($tenantB, $assetsB, 'Cash', '1000');
        $salesB = $this->createLedger($tenantB, $revenueB, 'Sales', '4000');

        $service = $this->app->make(AccountBalanceService::class);

        // Check Tenant A's balance under Tenant A's context.
        $this->forTenant($tenantA);
        $this->assertEqualsWithDelta(400.00, $service->balanceForLedger($cashA->fresh(['accountCategory'])), 0.001);

        // Tenant B has no transactions — balance must be zero.
        $this->forTenant($tenantB);
        $this->assertEqualsWithDelta(0.00, $service->balanceForLedger($cashB->fresh(['accountCategory'])), 0.001);
    }
}
