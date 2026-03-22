<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Transactions;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\Concerns\InteractsWithInventory;
use Tests\TestCase;

class CreateTransactionTest extends TestCase
{
    use InteractsWithInventory, RefreshDatabase;

    public function test_create_transaction_page_renders(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get(route('transactions.create'))
            ->assertOk();
    }

    public function test_balanced_transaction_can_be_posted(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $ledgers = $tenant->settings['ledgers'];

        Volt::actingAs($user)
            ->test('transactions.create')
            ->set('date', now()->toDateString())
            ->set('description', 'Office rent payment')
            ->set('lines.0.ledger_id', $ledgers['operating_expenses'])
            ->set('lines.0.direction', 'in')
            ->set('lines.0.amount', '5000')
            ->set('lines.1.ledger_id', $ledgers['cash'])
            ->set('lines.1.direction', 'out')
            ->set('lines.1.amount', '5000')
            ->call('post')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('entries', [
            'description' => 'Office rent payment',
        ]);
    }

    public function test_description_is_required(): void
    {
        $tenant = $this->createTenantWithChartOfAccounts();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        Volt::actingAs($user)
            ->test('transactions.create')
            ->set('date', now()->toDateString())
            ->set('description', '')
            ->call('post')
            ->assertHasErrors(['description' => 'required']);
    }
}
