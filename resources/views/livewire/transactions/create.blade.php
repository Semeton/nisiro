<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use Modules\Bookkeeping\Actions\PostTransactionAction;
use Modules\Bookkeeping\Enums\LineType;
use Modules\Bookkeeping\Exceptions\UnbalancedEntryException;
use Modules\Bookkeeping\Models\Ledger;

new class extends Component {
    public string $date = '';
    public string $description = '';
    public string $reference = '';
    public array $lines = [];

    public function mount(): void
    {
        $this->date = now()->toDateString();
        $this->lines = [
            ['ledger_id' => '', 'direction' => 'in', 'amount' => ''],
            ['ledger_id' => '', 'direction' => 'out', 'amount' => ''],
        ];
    }

    public function addLine(): void
    {
        $this->lines[] = ['ledger_id' => '', 'direction' => 'in', 'amount' => ''];
    }

    public function removeLine(int $index): void
    {
        if (count($this->lines) > 2) {
            array_splice($this->lines, $index, 1);
        }
    }

    public function getTotalInProperty(): float
    {
        return collect($this->lines)
            ->where('direction', 'in')
            ->sum(fn ($l) => (float) ($l['amount'] ?: 0));
    }

    public function getTotalOutProperty(): float
    {
        return collect($this->lines)
            ->where('direction', 'out')
            ->sum(fn ($l) => (float) ($l['amount'] ?: 0));
    }

    public function getIsBalancedProperty(): bool
    {
        return abs($this->totalIn - $this->totalOut) < 0.01 && $this->totalIn > 0;
    }

    public function post(): void
    {
        $this->validate([
            'date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:100'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.ledger_id' => ['required', 'exists:ledgers,id'],
            'lines.*.direction' => ['required', 'in:in,out'],
            'lines.*.amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $entryLines = collect($this->lines)->map(fn ($line) => [
            'ledger_id' => $line['ledger_id'],
            'type' => $line['direction'] === 'in' ? LineType::Debit : LineType::Credit,
            'amount' => (float) $line['amount'],
        ])->all();

        try {
            $entry = app(PostTransactionAction::class)->execute(
                date: $this->date,
                description: $this->description,
                lines: $entryLines,
                reference: $this->reference ?: null,
            );
        } catch (UnbalancedEntryException) {
            $this->addError('lines', 'The money in and money out amounts must be equal.');

            return;
        }

        session()->flash('status', 'Transaction recorded successfully.');

        $this->redirect(route('transactions.show', $entry), navigate: true);
    }

    public function with(): array
    {
        return [
            'ledgers' => Ledger::with('accountCategory')->orderBy('code')->get(),
        ];
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col">
        <x-page-heading heading="New Transaction" subheading="Record a manual financial transaction" />

        <flux:callout class="mb-6">
            <flux:callout.text>
                {{ __('Each transaction must have equal amounts going in and out of accounts. This keeps your books balanced.') }}
            </flux:callout.text>
        </flux:callout>

        <form wire:submit="post" class="max-w-2xl space-y-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input wire:model="date" :label="__('Date')" type="date" required />
                <flux:input wire:model="reference" :label="__('Reference')" :description="__('Optional')" />
            </div>

            <flux:input wire:model="description" :label="__('What is this transaction for?')" required />

            <flux:separator />

            <div class="space-y-4">
                <flux:heading size="sm">{{ __('Transaction Lines') }}</flux:heading>

                @foreach ($lines as $index => $line)
                    <div wire:key="line-{{ $index }}" class="flex items-end gap-3 rounded-lg border border-neutral-200 p-4 dark:border-neutral-700">
                        <div class="flex-1">
                            <flux:select wire:model="lines.{{ $index }}.ledger_id" :label="__('Account')" placeholder="Select account..." required>
                                @foreach ($ledgers as $ledger)
                                    <flux:select.option :value="$ledger->id">
                                        {{ $ledger->code }} — {{ $ledger->name }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                        <div class="w-36">
                            <flux:radio.group wire:model.live="lines.{{ $index }}.direction" :label="__('Direction')" variant="segmented">
                                <flux:radio value="in" label="In" />
                                <flux:radio value="out" label="Out" />
                            </flux:radio.group>
                        </div>
                        <div class="w-36">
                            <flux:input wire:model.live.debounce.500ms="lines.{{ $index }}.amount" :label="__('Amount')" type="number" step="0.01" min="0.01" required />
                        </div>
                        @if (count($lines) > 2)
                            <flux:button variant="ghost" icon="x-mark" wire:click="removeLine({{ $index }})" size="sm" />
                        @endif
                    </div>
                @endforeach

                <flux:button variant="ghost" icon="plus" wire:click="addLine" size="sm">
                    {{ __('Add another line') }}
                </flux:button>
            </div>

            {{-- Balance Indicator --}}
            <div class="flex items-center gap-4 rounded-lg border border-neutral-200 p-4 dark:border-neutral-700">
                <div class="flex-1">
                    <flux:text class="text-sm">{{ __('Money In') }}: <strong>@money($this->totalIn)</strong></flux:text>
                </div>
                <div class="flex-1">
                    <flux:text class="text-sm">{{ __('Money Out') }}: <strong>@money($this->totalOut)</strong></flux:text>
                </div>
                <div>
                    @if ($this->isBalanced)
                        <flux:badge color="green" size="sm">{{ __('Balanced') }}</flux:badge>
                    @else
                        <flux:badge color="red" size="sm">{{ __('Not Balanced') }}</flux:badge>
                    @endif
                </div>
            </div>

            @error('lines')
                <flux:text class="text-red-600 dark:text-red-400">{{ $message }}</flux:text>
            @enderror

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit" :disabled="! $this->isBalanced">
                    {{ __('Record Transaction') }}
                </flux:button>
                <flux:button variant="ghost" :href="route('transactions.index')" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
            </div>
        </form>
</div>
