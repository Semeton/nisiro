<?php

declare(strict_types=1);

use Illuminate\Support\Number;
use Livewire\Volt\Component;
use Modules\Bookkeeping\Models\Entry;
use Modules\Bookkeeping\Models\Ledger;
use Modules\Bookkeeping\Services\AccountBalanceService;
use Modules\Inventory\Models\StockItem;

new class extends Component {
    public function with(): array
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;
        $ledgers = $tenant?->settings['ledgers'] ?? [];
        $balanceService = app(AccountBalanceService::class);

        $cashBalance = 0.0;
        $bankBalance = 0.0;

        if (! empty($ledgers['cash'])) {
            $cashLedger = Ledger::with('accountCategory')->find($ledgers['cash']);
            if ($cashLedger) {
                $cashBalance = $balanceService->balanceForLedger($cashLedger);
            }
        }

        if (! empty($ledgers['bank'])) {
            $bankLedger = Ledger::with('accountCategory')->find($ledgers['bank']);
            if ($bankLedger) {
                $bankBalance = $balanceService->balanceForLedger($bankLedger);
            }
        }

        $totalStockValue = (float) (StockItem::query()
            ->selectRaw('SUM(quantity_on_hand * cost_price) as total')
            ->value('total') ?? 0);

        $lowStockItems = StockItem::query()
            ->whereNotNull('reorder_level')
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->where('reorder_level', '>', 0)
            ->get();

        $recentTransactions = Entry::query()
            ->whereNotNull('posted_at')
            ->latest('date')
            ->limit(5)
            ->get();

        return [
            'cashBalance' => $cashBalance,
            'bankBalance' => $bankBalance,
            'totalStockValue' => $totalStockValue,
            'lowStockItems' => $lowStockItems,
            'recentTransactions' => $recentTransactions,
        ];
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Summary Cards --}}
        <div class="grid gap-4 md:grid-cols-3">
            <x-stat-card label="Cash on Hand" :value="Number::currency($cashBalance, 'NGN')" />
            <x-stat-card label="Bank Balance" :value="Number::currency($bankBalance, 'NGN')" />
            <x-stat-card label="Total Stock Value" :value="Number::currency($totalStockValue, 'NGN')" />
        </div>

        {{-- Quick Actions --}}
        <div class="flex flex-wrap gap-3">
            <flux:button variant="primary" :href="route('products.create')" wire:navigate icon="plus">
                {{ __('Add Product') }}
            </flux:button>
            <flux:button :href="route('stock.purchase')" wire:navigate icon="arrow-down-tray">
                {{ __('Record Purchase') }}
            </flux:button>
            <flux:button :href="route('stock.sell')" wire:navigate icon="arrow-up-tray">
                {{ __('Record Sale') }}
            </flux:button>
        </div>

        {{-- Low Stock Alerts --}}
        @if ($lowStockItems->count() > 0)
            <flux:callout variant="warning" icon="exclamation-triangle">
                <flux:callout.heading>{{ __('Low Stock Alert') }}</flux:callout.heading>
                <flux:callout.text>
                    @foreach ($lowStockItems as $item)
                        <span class="block">{{ $item->name }} — {{ number_format((float) $item->quantity_on_hand) }} left (reorder at {{ number_format((float) $item->reorder_level) }})</span>
                    @endforeach
                </flux:callout.text>
            </flux:callout>
        @endif

        {{-- Recent Transactions --}}
        <div>
            <div class="mb-3 flex items-center justify-between">
                <flux:heading size="lg">{{ __('Recent Transactions') }}</flux:heading>
                <flux:button variant="ghost" :href="route('transactions.index')" wire:navigate size="sm">
                    {{ __('View All') }}
                </flux:button>
            </div>

            @if ($recentTransactions->count() > 0)
                <div class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-neutral-200 bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-900">
                            <tr>
                                <th class="px-4 py-3 font-medium">{{ __('Date') }}</th>
                                <th class="px-4 py-3 font-medium">{{ __('Description') }}</th>
                                <th class="px-4 py-3 font-medium text-right">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                            @foreach ($recentTransactions as $entry)
                                <tr wire:key="entry-{{ $entry->id }}" class="cursor-pointer hover:bg-neutral-50 dark:hover:bg-zinc-800/50" onclick="window.Livewire.navigate('{{ route('transactions.show', $entry) }}')">
                                    <td class="px-4 py-3">{{ $entry->date->format('d M Y') }}</td>
                                    <td class="px-4 py-3">{{ $entry->description }}</td>
                                    <td class="px-4 py-3 text-right">@money($entry->lines->sum('amount') / 2)</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-empty-state message="No transactions yet. Start by recording a purchase or sale." />
            @endif
        </div>
</div>
