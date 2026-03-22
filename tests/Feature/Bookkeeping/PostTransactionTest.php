<?php

declare(strict_types=1);

namespace Tests\Feature\Bookkeeping;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Bookkeeping\Actions\PostTransactionAction;
use Modules\Bookkeeping\Enums\AccountType;
use Modules\Bookkeeping\Exceptions\UnbalancedEntryException;
use Tests\Concerns\InteractsWithBookkeeping;
use Tests\TestCase;

class PostTransactionTest extends TestCase
{
    use InteractsWithBookkeeping, RefreshDatabase;

    public function test_balanced_transaction_is_posted_successfully(): void
    {
        $tenant = $this->createTenant();
        $this->forTenant($tenant);

        $assets = $this->createAccountCategory($tenant, 'Assets', AccountType::Assets);
        $revenue = $this->createAccountCategory($tenant, 'Revenue', AccountType::Revenue);
        $cash = $this->createLedger($tenant, $assets, 'Cash', '1000');
        $salesRevenue = $this->createLedger($tenant, $revenue, 'Sales Revenue', '4000');

        $action = $this->app->make(PostTransactionAction::class);

        $entry = $action->execute(
            date: '2026-03-15',
            description: 'Cash sale',
            lines: [
                $this->debit($cash, 500.00),
                $this->credit($salesRevenue, 500.00),
            ],
        );

        $this->assertNotNull($entry->posted_at);
        $this->assertDatabaseHas('entries', ['id' => $entry->id, 'description' => 'Cash sale']);
        $this->assertDatabaseHas('entry_lines', ['entry_id' => $entry->id, 'type' => 'debit', 'amount' => '500.00']);
        $this->assertDatabaseHas('entry_lines', ['entry_id' => $entry->id, 'type' => 'credit', 'amount' => '500.00']);
    }

    public function test_unbalanced_transaction_is_rejected(): void
    {
        $tenant = $this->createTenant();
        $this->forTenant($tenant);

        $assets = $this->createAccountCategory($tenant, 'Assets', AccountType::Assets);
        $cash = $this->createLedger($tenant, $assets, 'Cash', '1000');
        $bank = $this->createLedger($tenant, $assets, 'Bank', '1010');

        $action = $this->app->make(PostTransactionAction::class);

        $this->expectException(UnbalancedEntryException::class);

        $action->execute(
            date: '2026-03-15',
            description: 'Unbalanced entry',
            lines: [
                $this->debit($cash, 500.00),
                $this->credit($bank, 400.00), // intentionally wrong
            ],
        );
    }

    public function test_unbalanced_transaction_does_not_persist_anything(): void
    {
        $tenant = $this->createTenant();
        $this->forTenant($tenant);

        $assets = $this->createAccountCategory($tenant, 'Assets', AccountType::Assets);
        $cash = $this->createLedger($tenant, $assets, 'Cash', '1000');
        $bank = $this->createLedger($tenant, $assets, 'Bank', '1010');

        $action = $this->app->make(PostTransactionAction::class);

        try {
            $action->execute(
                date: '2026-03-15',
                description: 'Unbalanced entry',
                lines: [
                    $this->debit($cash, 500.00),
                    $this->credit($bank, 400.00),
                ],
            );
        } catch (UnbalancedEntryException) {
            // expected
        }

        $this->assertDatabaseMissing('entries', ['description' => 'Unbalanced entry']);
    }

    public function test_multi_line_balanced_transaction_is_posted(): void
    {
        $tenant = $this->createTenant();
        $this->forTenant($tenant);

        $assets = $this->createAccountCategory($tenant, 'Assets', AccountType::Assets);
        $expense = $this->createAccountCategory($tenant, 'Expense', AccountType::Expense);
        $revenue = $this->createAccountCategory($tenant, 'Revenue', AccountType::Revenue);

        $cash = $this->createLedger($tenant, $assets, 'Cash', '1000');
        $rent = $this->createLedger($tenant, $expense, 'Rent Expense', '6000');
        $salesRevenue = $this->createLedger($tenant, $revenue, 'Sales Revenue', '4000');

        $action = $this->app->make(PostTransactionAction::class);

        $entry = $action->execute(
            date: '2026-03-15',
            description: 'Revenue and expense in one entry',
            lines: [
                $this->debit($cash, 800.00),
                $this->debit($rent, 200.00),
                $this->credit($salesRevenue, 1000.00),
            ],
        );

        $this->assertCount(3, $entry->lines);
        $this->assertNotNull($entry->posted_at);
    }

    public function test_optional_reference_is_stored(): void
    {
        $tenant = $this->createTenant();
        $this->forTenant($tenant);

        $assets = $this->createAccountCategory($tenant, 'Assets', AccountType::Assets);
        $revenue = $this->createAccountCategory($tenant, 'Revenue', AccountType::Revenue);
        $cash = $this->createLedger($tenant, $assets, 'Cash', '1000');
        $salesRevenue = $this->createLedger($tenant, $revenue, 'Sales Revenue', '4000');

        $action = $this->app->make(PostTransactionAction::class);

        $entry = $action->execute(
            date: '2026-03-15',
            description: 'Invoice payment',
            lines: [
                $this->debit($cash, 100.00),
                $this->credit($salesRevenue, 100.00),
            ],
            reference: 'INV-001',
        );

        $this->assertDatabaseHas('entries', ['id' => $entry->id, 'reference' => 'INV-001']);
    }
}
