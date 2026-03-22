<?php

declare(strict_types=1);

namespace Tests\Feature\Bookkeeping;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Bookkeeping\Actions\PostTransactionAction;
use Modules\Bookkeeping\Enums\AccountType;
use Modules\Bookkeeping\Exceptions\ImmutableEntryException;
use Modules\Bookkeeping\Models\Entry;
use Tests\Concerns\InteractsWithBookkeeping;
use Tests\TestCase;

class EntryImmutabilityTest extends TestCase
{
    use InteractsWithBookkeeping, RefreshDatabase;

    public function test_posted_entry_cannot_be_updated(): void
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
            description: 'Original description',
            lines: [
                $this->debit($cash, 100.00),
                $this->credit($salesRevenue, 100.00),
            ],
        );

        $this->expectException(ImmutableEntryException::class);

        $entry->update(['description' => 'Tampered description']);
    }

    public function test_posted_entry_cannot_be_deleted(): void
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
            description: 'Entry to delete',
            lines: [
                $this->debit($cash, 100.00),
                $this->credit($salesRevenue, 100.00),
            ],
        );

        $this->expectException(ImmutableEntryException::class);

        $entry->delete();
    }

    public function test_draft_entry_can_be_updated(): void
    {
        $tenant = $this->createTenant();
        $this->forTenant($tenant);

        $entry = Entry::create([
            'tenant_id' => $tenant->id,
            'date' => '2026-03-15',
            'description' => 'Draft entry',
            'posted_at' => null,
        ]);

        $entry->update(['description' => 'Updated draft']);

        $this->assertDatabaseHas('entries', ['id' => $entry->id, 'description' => 'Updated draft']);
    }

    public function test_draft_entry_can_be_deleted(): void
    {
        $tenant = $this->createTenant();
        $this->forTenant($tenant);

        $entry = Entry::create([
            'tenant_id' => $tenant->id,
            'date' => '2026-03-15',
            'description' => 'Draft entry',
            'posted_at' => null,
        ]);

        $entry->delete();

        $this->assertDatabaseMissing('entries', ['id' => $entry->id]);
    }

    public function test_entry_line_of_posted_entry_cannot_be_deleted(): void
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
                $this->debit($cash, 100.00),
                $this->credit($salesRevenue, 100.00),
            ],
        );

        $line = $entry->lines->first();

        $this->expectException(ImmutableEntryException::class);

        $line->delete();
    }
}
