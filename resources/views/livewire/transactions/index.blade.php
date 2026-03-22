<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Modules\Bookkeeping\Models\Entry;

new class extends Component {
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $entries = Entry::query()
            ->with('lines')
            ->when($this->search, fn ($q) => $q->where('description', 'like', '%' . $this->search . '%'))
            ->latest('date')
            ->paginate(20);

        return ['entries' => $entries];
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col">
        <x-page-heading heading="Transactions" subheading="All financial transactions">
            <x-slot:actions>
                <flux:button variant="primary" :href="route('transactions.create')" wire:navigate icon="plus">
                    {{ __('New Transaction') }}
                </flux:button>
            </x-slot:actions>
        </x-page-heading>

        @if ($entries->total() > 0 || $search)
            <div class="mb-4">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Search transactions..." icon="magnifying-glass" />
            </div>
        @endif

        @if ($entries->count() > 0)
            <div class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-neutral-200 bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-900">
                        <tr>
                            <th class="px-4 py-3 font-medium">{{ __('Date') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('Description') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('Reference') }}</th>
                            <th class="px-4 py-3 font-medium text-center">{{ __('Status') }}</th>
                            <th class="px-4 py-3 font-medium text-right">{{ __('Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @foreach ($entries as $entry)
                            <tr wire:key="entry-{{ $entry->id }}" class="cursor-pointer hover:bg-neutral-50 dark:hover:bg-zinc-800/50" onclick="window.Livewire.navigate('{{ route('transactions.show', $entry) }}')">
                                <td class="px-4 py-3">{{ $entry->date->format('d M Y') }}</td>
                                <td class="px-4 py-3 font-medium">{{ $entry->description }}</td>
                                <td class="px-4 py-3 text-neutral-500 dark:text-neutral-400">{{ $entry->reference ?? '—' }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if ($entry->isPosted())
                                        <flux:badge color="green" size="sm">{{ __('Posted') }}</flux:badge>
                                    @else
                                        <flux:badge color="yellow" size="sm">{{ __('Draft') }}</flux:badge>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">@money($entry->lines->where('type', \Modules\Bookkeeping\Enums\LineType::Debit)->sum('amount'))</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $entries->links() }}
            </div>
        @elseif ($search)
            <x-empty-state message="No transactions match your search." />
        @else
            <x-empty-state
                message="No transactions yet. Record a purchase or sale to see transactions here."
                action-label="New Transaction"
                :action-url="route('transactions.create')"
            />
        @endif
</div>
