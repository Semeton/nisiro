<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use Modules\Inventory\Models\StockItem;

new class extends Component {
    public StockItem $stockItem;

    public function mount(StockItem $stockItem): void
    {
        $this->stockItem = $stockItem;
    }

    public function with(): array
    {
        $movements = $this->stockItem->movements()
            ->latest()
            ->limit(10)
            ->get();

        return ['movements' => $movements];
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col">
        <flux:breadcrumbs class="mb-4">
            <flux:breadcrumbs.item :href="route('products.index')" wire:navigate>{{ __('Products') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $stockItem->name }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <div class="mb-6 flex items-start justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ $stockItem->name }}</flux:heading>
                <flux:text class="mt-1">{{ __('SKU') }}: {{ $stockItem->sku }}</flux:text>
            </div>
            <div class="flex gap-2">
                <flux:button variant="primary" :href="route('stock.purchase', ['product' => $stockItem->id])" wire:navigate icon="arrow-down-tray" size="sm">
                    {{ __('Stock In') }}
                </flux:button>
                <flux:button :href="route('stock.sell', ['product' => $stockItem->id])" wire:navigate icon="arrow-up-tray" size="sm">
                    {{ __('Stock Out') }}
                </flux:button>
                <flux:button :href="route('stock.adjust', ['product' => $stockItem->id])" wire:navigate size="sm">
                    {{ __('Adjust') }}
                </flux:button>
            </div>
        </div>

        {{-- Product Info Cards --}}
        <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card label="In Stock" :value="number_format((float) $stockItem->quantity_on_hand)" />
            <x-stat-card label="Cost Price" :value="Number::currency((float) $stockItem->cost_price, 'NGN')" />
            <x-stat-card label="Selling Price" :value="Number::currency((float) $stockItem->selling_price, 'NGN')" />
            <x-stat-card label="Reorder Level" :value="$stockItem->reorder_level ? number_format((float) $stockItem->reorder_level) : '—'" />
        </div>

        {{-- Recent Movements --}}
        <flux:heading size="lg" level="2" class="mb-4">{{ __('Recent Movements') }}</flux:heading>

        @if ($movements->count() > 0)
            <div class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-neutral-200 bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-900">
                        <tr>
                            <th class="px-4 py-3 font-medium">{{ __('Date') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('Type') }}</th>
                            <th class="px-4 py-3 font-medium text-right">{{ __('Quantity') }}</th>
                            <th class="px-4 py-3 font-medium text-right">{{ __('Unit Cost') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('Notes') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @foreach ($movements as $movement)
                            <tr wire:key="movement-{{ $movement->id }}">
                                <td class="px-4 py-3">{{ $movement->created_at->format('d M Y') }}</td>
                                <td class="px-4 py-3">
                                    @if ($movement->type->value === 'purchase')
                                        <flux:badge color="green" size="sm">{{ __('Purchase') }}</flux:badge>
                                    @elseif ($movement->type->value === 'sale')
                                        <flux:badge color="red" size="sm">{{ __('Sale') }}</flux:badge>
                                    @else
                                        <flux:badge color="yellow" size="sm">{{ __('Adjustment') }}</flux:badge>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right {{ (float) $movement->quantity > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ (float) $movement->quantity > 0 ? '+' : '' }}{{ number_format((float) $movement->quantity) }}
                                </td>
                                <td class="px-4 py-3 text-right">@money($movement->unit_cost)</td>
                                <td class="px-4 py-3 text-neutral-500 dark:text-neutral-400">{{ $movement->notes ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-empty-state message="No stock movements yet for this product." />
        @endif
</div>
