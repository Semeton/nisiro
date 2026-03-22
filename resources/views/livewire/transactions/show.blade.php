<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use Modules\Bookkeeping\Enums\LineType;
use Modules\Bookkeeping\Models\Entry;

new class extends Component {
    public Entry $entry;

    public function mount(Entry $entry): void
    {
        $this->entry = $entry->load('lines.ledger.accountCategory');
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col">
        <flux:breadcrumbs class="mb-4">
            <flux:breadcrumbs.item :href="route('transactions.index')" wire:navigate>{{ __('Transactions') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ Str::limit($entry->description, 40) }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <div class="mb-6">
            <flux:heading size="xl" level="1">{{ $entry->description }}</flux:heading>
            <div class="mt-2 flex items-center gap-4">
                <flux:text>{{ $entry->date->format('d M Y') }}</flux:text>
                @if ($entry->reference)
                    <flux:text>{{ __('Ref') }}: {{ $entry->reference }}</flux:text>
                @endif
                @if ($entry->isPosted())
                    <flux:badge color="green" size="sm">{{ __('Posted') }}</flux:badge>
                @else
                    <flux:badge color="yellow" size="sm">{{ __('Draft') }}</flux:badge>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-neutral-200 bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-900">
                    <tr>
                        <th class="px-4 py-3 font-medium">{{ __('Account') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Code') }}</th>
                        <th class="px-4 py-3 font-medium text-center">{{ __('Direction') }}</th>
                        <th class="px-4 py-3 font-medium text-right">{{ __('Amount') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @foreach ($entry->lines as $line)
                        <tr wire:key="line-{{ $line->id }}">
                            <td class="px-4 py-3 font-medium">{{ $line->ledger->name }}</td>
                            <td class="px-4 py-3 text-neutral-500 dark:text-neutral-400">{{ $line->ledger->code }}</td>
                            <td class="px-4 py-3 text-center">
                                @if ($line->type === LineType::Debit)
                                    <flux:badge color="blue" size="sm">{{ __('Money In') }}</flux:badge>
                                @else
                                    <flux:badge color="amber" size="sm">{{ __('Money Out') }}</flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-medium">@money($line->amount)</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t border-neutral-200 bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-900">
                    <tr>
                        <td class="px-4 py-3 font-medium" colspan="3">{{ __('Total') }}</td>
                        <td class="px-4 py-3 text-right font-bold">
                            @money($entry->lines->where('type', LineType::Debit)->sum('amount'))
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
</div>
