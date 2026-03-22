<?php

declare(strict_types=1);

namespace Tests\Feature\Bookkeeping;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Bookkeeping\Enums\AccountType;
use Modules\Bookkeeping\Enums\LineType;
use Modules\Bookkeeping\Exceptions\UnbalancedEntryException;
use Modules\Bookkeeping\Services\DoubleEntryService;
use Tests\Concerns\InteractsWithBookkeeping;
use Tests\TestCase;

class DoubleEntryTest extends TestCase
{
    use InteractsWithBookkeeping, RefreshDatabase;

    private DoubleEntryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(DoubleEntryService::class);
    }

    public function test_balanced_lines_pass_validation(): void
    {
        $lines = [
            ['type' => LineType::Debit, 'amount' => 500.00],
            ['type' => LineType::Credit, 'amount' => 500.00],
        ];

        $this->assertTrue($this->service->isBalanced($lines));
    }

    public function test_unbalanced_lines_fail_validation(): void
    {
        $lines = [
            ['type' => LineType::Debit, 'amount' => 500.00],
            ['type' => LineType::Credit, 'amount' => 300.00],
        ];

        $this->assertFalse($this->service->isBalanced($lines));
    }

    public function test_multiple_lines_that_balance_pass_validation(): void
    {
        $lines = [
            ['type' => LineType::Debit, 'amount' => 1000.00],
            ['type' => LineType::Credit, 'amount' => 700.00],
            ['type' => LineType::Credit, 'amount' => 300.00],
        ];

        $this->assertTrue($this->service->isBalanced($lines));
    }

    public function test_validate_or_fail_throws_on_unbalanced_entry(): void
    {
        $this->expectException(UnbalancedEntryException::class);

        $lines = [
            ['type' => LineType::Debit, 'amount' => 200.00],
            ['type' => LineType::Credit, 'amount' => 100.00],
        ];

        $this->service->validateOrFail($lines);
    }

    public function test_validate_or_fail_passes_on_balanced_entry(): void
    {
        $lines = [
            ['type' => LineType::Debit, 'amount' => 250.00],
            ['type' => LineType::Credit, 'amount' => 250.00],
        ];

        $this->service->validateOrFail($lines); // No exception thrown

        $this->assertTrue(true);
    }

    public function test_zero_amount_entry_is_balanced(): void
    {
        $lines = [
            ['type' => LineType::Debit, 'amount' => 0.00],
            ['type' => LineType::Credit, 'amount' => 0.00],
        ];

        $this->assertTrue($this->service->isBalanced($lines));
    }

    public function test_normal_balance_is_debit_for_assets_and_expense(): void
    {
        $this->assertSame(LineType::Debit, AccountType::Assets->normalBalance());
        $this->assertSame(LineType::Debit, AccountType::Expense->normalBalance());
    }

    public function test_normal_balance_is_credit_for_liabilities_equity_and_revenue(): void
    {
        $this->assertSame(LineType::Credit, AccountType::Liabilities->normalBalance());
        $this->assertSame(LineType::Credit, AccountType::Equity->normalBalance());
        $this->assertSame(LineType::Credit, AccountType::Revenue->normalBalance());
    }
}
