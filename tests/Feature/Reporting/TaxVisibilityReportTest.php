<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Bookkeeping\Actions\PostTransactionAction;
use Modules\Bookkeeping\Enums\AccountType;
use Modules\Reporting\Services\TaxVisibilityReportService;
use Tests\Concerns\InteractsWithBookkeeping;
use Tests\TestCase;

class TaxVisibilityReportTest extends TestCase
{
    use InteractsWithBookkeeping, RefreshDatabase;

    private TaxVisibilityReportService $service;

    private PostTransactionAction $postAction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(TaxVisibilityReportService::class);
        $this->postAction = $this->app->make(PostTransactionAction::class);
    }

    public function test_vat_applicable_when_revenue_exceeds_threshold(): void
    {
        $tenant = $this->createTenant();
        $this->forTenant($tenant);

        $assets = $this->createAccountCategory($tenant, 'Assets', AccountType::Assets);
        $revenue = $this->createAccountCategory($tenant, 'Revenue', AccountType::Revenue);

        $cash = $this->createLedger($tenant, $assets, 'Cash', '1000');
        $sales = $this->createLedger($tenant, $revenue, 'Sales Revenue', '4000');

        // Revenue over 25M NGN
        $this->postAction->execute('2026-03-15', 'Large sale', [
            $this->debit($cash, 30_000_000.00),
            $this->credit($sales, 30_000_000.00),
        ]);

        $report = $this->service->generate(
            $tenant,
            Carbon::parse('2026-03-01'),
            Carbon::parse('2026-03-31'),
        );

        $vat = collect($report['obligations'])->firstWhere('name', 'VAT Registration');
        $this->assertTrue($vat['applicable']);
        $this->assertStringContains('25,000,000', $vat['reason']);
    }

    public function test_vat_not_applicable_when_revenue_below_threshold(): void
    {
        $tenant = $this->createTenant();
        $this->forTenant($tenant);

        $assets = $this->createAccountCategory($tenant, 'Assets', AccountType::Assets);
        $revenue = $this->createAccountCategory($tenant, 'Revenue', AccountType::Revenue);

        $cash = $this->createLedger($tenant, $assets, 'Cash', '1000');
        $sales = $this->createLedger($tenant, $revenue, 'Sales Revenue', '4000');

        // Revenue under 25M NGN
        $this->postAction->execute('2026-03-15', 'Small sale', [
            $this->debit($cash, 500_000.00),
            $this->credit($sales, 500_000.00),
        ]);

        $report = $this->service->generate(
            $tenant,
            Carbon::parse('2026-03-01'),
            Carbon::parse('2026-03-31'),
        );

        $vat = collect($report['obligations'])->firstWhere('name', 'VAT Registration');
        $this->assertFalse($vat['applicable']);
    }

    public function test_income_tax_applicable_when_profit_is_positive(): void
    {
        $tenant = $this->createTenant();
        $this->forTenant($tenant);

        $assets = $this->createAccountCategory($tenant, 'Assets', AccountType::Assets);
        $revenue = $this->createAccountCategory($tenant, 'Revenue', AccountType::Revenue);
        $expense = $this->createAccountCategory($tenant, 'Expenses', AccountType::Expense);

        $cash = $this->createLedger($tenant, $assets, 'Cash', '1000');
        $sales = $this->createLedger($tenant, $revenue, 'Sales Revenue', '4000');
        $cogs = $this->createLedger($tenant, $expense, 'COGS', '5000');

        $this->postAction->execute('2026-03-10', 'Sale', [
            $this->debit($cash, 10000.00),
            $this->credit($sales, 10000.00),
        ]);

        $this->postAction->execute('2026-03-12', 'Purchase', [
            $this->debit($cogs, 3000.00),
            $this->credit($cash, 3000.00),
        ]);

        $report = $this->service->generate(
            $tenant,
            Carbon::parse('2026-03-01'),
            Carbon::parse('2026-03-31'),
        );

        $incomeTax = collect($report['obligations'])->firstWhere('name', 'Company Income Tax');
        $this->assertTrue($incomeTax['applicable']);
        $this->assertEqualsWithDelta(7000.00, $report['net_profit'], 0.01);
    }

    public function test_income_tax_not_applicable_when_no_profit(): void
    {
        $tenant = $this->createTenant();
        $this->forTenant($tenant);

        $assets = $this->createAccountCategory($tenant, 'Assets', AccountType::Assets);
        $revenue = $this->createAccountCategory($tenant, 'Revenue', AccountType::Revenue);
        $expense = $this->createAccountCategory($tenant, 'Expenses', AccountType::Expense);

        $cash = $this->createLedger($tenant, $assets, 'Cash', '1000');
        $sales = $this->createLedger($tenant, $revenue, 'Sales Revenue', '4000');
        $cogs = $this->createLedger($tenant, $expense, 'COGS', '5000');

        // Break even
        $this->postAction->execute('2026-03-10', 'Sale', [
            $this->debit($cash, 5000.00),
            $this->credit($sales, 5000.00),
        ]);

        $this->postAction->execute('2026-03-12', 'Purchase', [
            $this->debit($cogs, 5000.00),
            $this->credit($cash, 5000.00),
        ]);

        $report = $this->service->generate(
            $tenant,
            Carbon::parse('2026-03-01'),
            Carbon::parse('2026-03-31'),
        );

        $incomeTax = collect($report['obligations'])->firstWhere('name', 'Company Income Tax');
        $this->assertFalse($incomeTax['applicable']);
    }

    public function test_returns_all_obligation_types(): void
    {
        $tenant = $this->createTenant();
        $this->forTenant($tenant);

        $report = $this->service->generate(
            $tenant,
            Carbon::parse('2026-03-01'),
            Carbon::parse('2026-03-31'),
        );

        $names = array_column($report['obligations'], 'name');
        $this->assertContains('VAT Registration', $names);
        $this->assertContains('Company Income Tax', $names);
        $this->assertContains('Withholding Tax', $names);
    }

    /**
     * Custom assertion for string containment.
     */
    private function assertStringContains(string $needle, string $haystack): void
    {
        $this->assertStringContainsString($needle, $haystack);
    }
}
