<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Bookkeeping\Actions\PostTransactionAction;
use Modules\Bookkeeping\Enums\AccountType;
use Modules\Bookkeeping\Models\Entry;
use Modules\Reporting\Services\ProfitAndLossReportService;
use Tests\Concerns\InteractsWithBookkeeping;
use Tests\TestCase;

class ProfitAndLossReportTest extends TestCase
{
    use InteractsWithBookkeeping, RefreshDatabase;

    private ProfitAndLossReportService $service;

    private PostTransactionAction $postAction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(ProfitAndLossReportService::class);
        $this->postAction = $this->app->make(PostTransactionAction::class);
    }

    public function test_calculates_net_profit_correctly(): void
    {
        $tenant = $this->createTenant();
        $this->forTenant($tenant);

        $assets = $this->createAccountCategory($tenant, 'Assets', AccountType::Assets);
        $revenue = $this->createAccountCategory($tenant, 'Revenue', AccountType::Revenue);
        $expense = $this->createAccountCategory($tenant, 'Expenses', AccountType::Expense);

        $cash = $this->createLedger($tenant, $assets, 'Cash', '1000');
        $sales = $this->createLedger($tenant, $revenue, 'Sales Revenue', '4000');
        $cogs = $this->createLedger($tenant, $expense, 'COGS', '5000');

        // Revenue: 5000
        $this->postAction->execute('2026-03-10', 'Sale', [
            $this->debit($cash, 5000.00),
            $this->credit($sales, 5000.00),
        ]);

        // Expense: 2000
        $this->postAction->execute('2026-03-12', 'Purchase', [
            $this->debit($cogs, 2000.00),
            $this->credit($cash, 2000.00),
        ]);

        $report = $this->service->generate(
            $tenant,
            Carbon::parse('2026-03-01'),
            Carbon::parse('2026-03-31'),
        );

        $this->assertEqualsWithDelta(5000.00, $report['revenue']['total'], 0.01);
        $this->assertEqualsWithDelta(2000.00, $report['expenses']['total'], 0.01);
        $this->assertEqualsWithDelta(3000.00, $report['net_profit'], 0.01);
    }

    public function test_respects_date_range(): void
    {
        $tenant = $this->createTenant();
        $this->forTenant($tenant);

        $assets = $this->createAccountCategory($tenant, 'Assets', AccountType::Assets);
        $revenue = $this->createAccountCategory($tenant, 'Revenue', AccountType::Revenue);

        $cash = $this->createLedger($tenant, $assets, 'Cash', '1000');
        $sales = $this->createLedger($tenant, $revenue, 'Sales Revenue', '4000');

        // Inside range
        $this->postAction->execute('2026-03-15', 'March sale', [
            $this->debit($cash, 1000.00),
            $this->credit($sales, 1000.00),
        ]);

        // Outside range
        $this->postAction->execute('2026-04-05', 'April sale', [
            $this->debit($cash, 2000.00),
            $this->credit($sales, 2000.00),
        ]);

        $report = $this->service->generate(
            $tenant,
            Carbon::parse('2026-03-01'),
            Carbon::parse('2026-03-31'),
        );

        $this->assertEqualsWithDelta(1000.00, $report['revenue']['total'], 0.01);
    }

    public function test_only_includes_posted_entries(): void
    {
        $tenant = $this->createTenant();
        $this->forTenant($tenant);

        $assets = $this->createAccountCategory($tenant, 'Assets', AccountType::Assets);
        $revenue = $this->createAccountCategory($tenant, 'Revenue', AccountType::Revenue);

        $cash = $this->createLedger($tenant, $assets, 'Cash', '1000');
        $sales = $this->createLedger($tenant, $revenue, 'Sales Revenue', '4000');

        // Posted entry
        $this->postAction->execute('2026-03-15', 'Posted sale', [
            $this->debit($cash, 1000.00),
            $this->credit($sales, 1000.00),
        ]);

        // Draft entry (not posted)
        $draft = Entry::create([
            'tenant_id' => $tenant->id,
            'date' => '2026-03-15',
            'description' => 'Draft sale',
            'posted_at' => null,
        ]);
        $draft->lines()->create([
            'tenant_id' => $tenant->id,
            'ledger_id' => $sales->id,
            'type' => \Modules\Bookkeeping\Enums\LineType::Credit,
            'amount' => 5000.00,
        ]);

        $report = $this->service->generate(
            $tenant,
            Carbon::parse('2026-03-01'),
            Carbon::parse('2026-03-31'),
        );

        $this->assertEqualsWithDelta(1000.00, $report['revenue']['total'], 0.01);
    }

    public function test_groups_by_ledger(): void
    {
        $tenant = $this->createTenant();
        $this->forTenant($tenant);

        $assets = $this->createAccountCategory($tenant, 'Assets', AccountType::Assets);
        $expense = $this->createAccountCategory($tenant, 'Expenses', AccountType::Expense);

        $cash = $this->createLedger($tenant, $assets, 'Cash', '1000');
        $cogs = $this->createLedger($tenant, $expense, 'COGS', '5000');
        $opex = $this->createLedger($tenant, $expense, 'Operating Expenses', '5100');

        $this->postAction->execute('2026-03-10', 'COGS expense', [
            $this->debit($cogs, 1000.00),
            $this->credit($cash, 1000.00),
        ]);

        $this->postAction->execute('2026-03-12', 'Opex expense', [
            $this->debit($opex, 500.00),
            $this->credit($cash, 500.00),
        ]);

        $report = $this->service->generate(
            $tenant,
            Carbon::parse('2026-03-01'),
            Carbon::parse('2026-03-31'),
        );

        $this->assertCount(2, $report['expenses']['items']);
        $this->assertEqualsWithDelta(1500.00, $report['expenses']['total'], 0.01);

        $ledgerNames = array_column($report['expenses']['items'], 'ledger');
        $this->assertContains('COGS', $ledgerNames);
        $this->assertContains('Operating Expenses', $ledgerNames);
    }

    public function test_is_isolated_between_tenants(): void
    {
        $tenantA = $this->createTenant(['name' => 'Tenant A']);
        $tenantB = $this->createTenant(['name' => 'Tenant B']);

        // Tenant A data
        $this->forTenant($tenantA);
        $assetsA = $this->createAccountCategory($tenantA, 'Assets', AccountType::Assets);
        $revenueA = $this->createAccountCategory($tenantA, 'Revenue', AccountType::Revenue);
        $cashA = $this->createLedger($tenantA, $assetsA, 'Cash', '1000');
        $salesA = $this->createLedger($tenantA, $revenueA, 'Sales', '4000');

        $this->postAction->execute('2026-03-15', 'A sale', [
            $this->debit($cashA, 3000.00),
            $this->credit($salesA, 3000.00),
        ]);

        // Tenant B — no data
        $this->forTenant($tenantB);
        $assetsB = $this->createAccountCategory($tenantB, 'Assets', AccountType::Assets);
        $revenueB = $this->createAccountCategory($tenantB, 'Revenue', AccountType::Revenue);
        $this->createLedger($tenantB, $assetsB, 'Cash', '1000');
        $this->createLedger($tenantB, $revenueB, 'Sales', '4000');

        $from = Carbon::parse('2026-03-01');
        $to = Carbon::parse('2026-03-31');

        $this->forTenant($tenantA);
        $reportA = $this->service->generate($tenantA, $from, $to);

        $this->forTenant($tenantB);
        $reportB = $this->service->generate($tenantB, $from, $to);

        $this->assertEqualsWithDelta(3000.00, $reportA['revenue']['total'], 0.01);
        $this->assertEqualsWithDelta(0.00, $reportB['revenue']['total'], 0.01);
    }

    public function test_excludes_asset_and_liability_accounts(): void
    {
        $tenant = $this->createTenant();
        $this->forTenant($tenant);

        $assets = $this->createAccountCategory($tenant, 'Assets', AccountType::Assets);
        $liabilities = $this->createAccountCategory($tenant, 'Liabilities', AccountType::Liabilities);
        $revenue = $this->createAccountCategory($tenant, 'Revenue', AccountType::Revenue);

        $cash = $this->createLedger($tenant, $assets, 'Cash', '1000');
        $ap = $this->createLedger($tenant, $liabilities, 'Accounts Payable', '2000');
        $sales = $this->createLedger($tenant, $revenue, 'Sales Revenue', '4000');

        $this->postAction->execute('2026-03-15', 'Sale on credit', [
            $this->debit($cash, 1000.00),
            $this->credit($sales, 1000.00),
        ]);

        $report = $this->service->generate(
            $tenant,
            Carbon::parse('2026-03-01'),
            Carbon::parse('2026-03-31'),
        );

        // Only revenue should appear; assets/liabilities are excluded
        $this->assertEqualsWithDelta(1000.00, $report['revenue']['total'], 0.01);
        $this->assertEmpty($report['expenses']['items']);
    }
}
