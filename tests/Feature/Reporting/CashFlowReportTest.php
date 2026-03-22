<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Bookkeeping\Actions\PostTransactionAction;
use Modules\Bookkeeping\Enums\AccountType;
use Modules\Reporting\Services\CashFlowReportService;
use Tests\Concerns\InteractsWithBookkeeping;
use Tests\TestCase;

class CashFlowReportTest extends TestCase
{
    use InteractsWithBookkeeping, RefreshDatabase;

    private CashFlowReportService $service;

    private PostTransactionAction $postAction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(CashFlowReportService::class);
        $this->postAction = $this->app->make(PostTransactionAction::class);
    }

    public function test_calculates_inflows_and_outflows(): void
    {
        $tenant = $this->createTenant();
        $this->forTenant($tenant);

        $assets = $this->createAccountCategory($tenant, 'Assets', AccountType::Assets);
        $revenue = $this->createAccountCategory($tenant, 'Revenue', AccountType::Revenue);
        $expense = $this->createAccountCategory($tenant, 'Expenses', AccountType::Expense);

        $cash = $this->createLedger($tenant, $assets, 'Cash', '1000');
        $sales = $this->createLedger($tenant, $revenue, 'Sales Revenue', '4000');
        $cogs = $this->createLedger($tenant, $expense, 'COGS', '5000');

        // Inflow: cash received from sale
        $this->postAction->execute('2026-03-10', 'Cash sale', [
            $this->debit($cash, 5000.00),
            $this->credit($sales, 5000.00),
        ]);

        // Outflow: cash paid for purchase
        $this->postAction->execute('2026-03-12', 'Cash purchase', [
            $this->debit($cogs, 2000.00),
            $this->credit($cash, 2000.00),
        ]);

        $report = $this->service->generate(
            $tenant,
            Carbon::parse('2026-03-01'),
            Carbon::parse('2026-03-31'),
        );

        $this->assertEqualsWithDelta(5000.00, $report['totals']['inflows'], 0.01);
        $this->assertEqualsWithDelta(2000.00, $report['totals']['outflows'], 0.01);
        $this->assertEqualsWithDelta(3000.00, $report['totals']['net'], 0.01);
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
            $this->debit($cash, 3000.00),
            $this->credit($sales, 3000.00),
        ]);

        $report = $this->service->generate(
            $tenant,
            Carbon::parse('2026-03-01'),
            Carbon::parse('2026-03-31'),
        );

        $this->assertEqualsWithDelta(1000.00, $report['totals']['inflows'], 0.01);
    }

    public function test_includes_both_cash_and_bank_ledgers(): void
    {
        $tenant = $this->createTenant();
        $this->forTenant($tenant);

        $assets = $this->createAccountCategory($tenant, 'Assets', AccountType::Assets);
        $revenue = $this->createAccountCategory($tenant, 'Revenue', AccountType::Revenue);

        $cash = $this->createLedger($tenant, $assets, 'Cash', '1000');
        $bank = $this->createLedger($tenant, $assets, 'Bank Account', '1010');
        $sales = $this->createLedger($tenant, $revenue, 'Sales Revenue', '4000');

        // Cash inflow
        $this->postAction->execute('2026-03-10', 'Cash sale', [
            $this->debit($cash, 1000.00),
            $this->credit($sales, 1000.00),
        ]);

        // Bank inflow
        $this->postAction->execute('2026-03-12', 'Bank sale', [
            $this->debit($bank, 2000.00),
            $this->credit($sales, 2000.00),
        ]);

        $report = $this->service->generate(
            $tenant,
            Carbon::parse('2026-03-01'),
            Carbon::parse('2026-03-31'),
        );

        $this->assertCount(2, $report['items']);
        $this->assertEqualsWithDelta(3000.00, $report['totals']['inflows'], 0.01);
    }

    public function test_excludes_non_cash_ledgers(): void
    {
        $tenant = $this->createTenant();
        $this->forTenant($tenant);

        $assets = $this->createAccountCategory($tenant, 'Assets', AccountType::Assets);
        $revenue = $this->createAccountCategory($tenant, 'Revenue', AccountType::Revenue);

        $ar = $this->createLedger($tenant, $assets, 'Accounts Receivable', '1100');
        $sales = $this->createLedger($tenant, $revenue, 'Sales Revenue', '4000');

        // This goes to AR, not Cash — should not appear in cash flow
        $this->postAction->execute('2026-03-15', 'Credit sale', [
            $this->debit($ar, 5000.00),
            $this->credit($sales, 5000.00),
        ]);

        $report = $this->service->generate(
            $tenant,
            Carbon::parse('2026-03-01'),
            Carbon::parse('2026-03-31'),
        );

        $this->assertEqualsWithDelta(0.00, $report['totals']['inflows'], 0.01);
        $this->assertEqualsWithDelta(0.00, $report['totals']['outflows'], 0.01);
    }
}
