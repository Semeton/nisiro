<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Modules\Inventory\Models\StockItem;
use Modules\Inventory\Models\StockMovement;

new class extends Component {
    use WithPagination;

    public string $productFilter = '';
    public string $typeFilter = '';

    public function updatedProductFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $movements = StockMovement::query()
            ->with('stockItem')
            ->when($this->productFilter, fn ($q) => $q->where('stock_item_id', $this->productFilter))
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->latest()
            ->paginate(20);

        $products = StockItem::orderBy('name')->get();

        return ['movements' => $movements, 'products' => $products];
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col">
        <x-page-heading heading="Stock History" subheading="See all stock movements" />

        <div class="mb-4 flex flex-col gap-4 sm:flex-row">
            <div class="sm:w-64">
                <flux:select wire:model.live="productFilter" placeholder="All Products">
                    <flux:select.option value="">{{ __('All Products') }}</flux:select.option>
                    @foreach ($products as $product)
                        <flux:select.option :value="$product->id">{{ $product->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <flux:radio.group wire:model.live="typeFilter" variant="segmented">
                <flux:radio value="" label="All" />
                <flux:radio value="purchase" label="Purchases" />
                <flux:radio value="sale" label="Sales" />
                <flux:radio value="adjustment" label="Adjustments" />
            </flux:radio.group>
        </div>

        @if ($movements->count() > 0)
            <div class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-neutral-200 bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-900">
                        <tr>
                            <th class="px-4 py-3 font-medium">{{ __('Date') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('Product') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('Type') }}</th>
                            <th class="px-4 py-3 font-medium text-right">{{ __('Quantity') }}</th>
                            <th class="px-4 py-3 font-medium text-right">{{ __('Unit Cost') }}</th>
                            <th class="px-4 py-3 font-medium text-right">{{ __('Total') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('Notes') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @foreach ($movements as $movement)
                            <tr wire:key="movement-{{ $movement->id }}">
                                <td class="px-4 py-3">{{ $movement->created_at->format('d M Y') }}</td>
                                <td class="px-4 py-3">{{ $movement->stockItem->name }}</td>
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
                                <td class="px-4 py-3 text-right">@money($movement->totalCost())</td>
                                <td class="px-4 py-3 text-neutral-500 dark:text-neutral-400">{{ $movement->notes ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $movements->links() }}
            </div>
        @else
            <x-empty-state message="No stock movements found." />
        @endif
</div>
