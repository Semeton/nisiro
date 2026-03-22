<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Modules\Inventory\Models\StockItem;

new class extends Component {
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $products = StockItem::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('sku', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate(15);

        return ['products' => $products];
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col">
        <x-page-heading heading="Products" subheading="Manage your products and stock levels">
            <x-slot:actions>
                <flux:button variant="primary" :href="route('products.create')" wire:navigate icon="plus">
                    {{ __('Add Product') }}
                </flux:button>
            </x-slot:actions>
        </x-page-heading>

        @if ($products->total() > 0 || $search)
            <div class="mb-4">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Search products..." icon="magnifying-glass" />
            </div>
        @endif

        @if ($products->count() > 0)
            <div class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-neutral-200 bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-900">
                        <tr>
                            <th class="px-4 py-3 font-medium">{{ __('Name') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('SKU') }}</th>
                            <th class="px-4 py-3 font-medium text-right">{{ __('In Stock') }}</th>
                            <th class="px-4 py-3 font-medium text-right">{{ __('Cost Price') }}</th>
                            <th class="px-4 py-3 font-medium text-right">{{ __('Selling Price') }}</th>
                            <th class="px-4 py-3 font-medium text-center">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @foreach ($products as $product)
                            <tr wire:key="product-{{ $product->id }}" class="cursor-pointer hover:bg-neutral-50 dark:hover:bg-zinc-800/50" onclick="window.Livewire.navigate('{{ route('products.show', $product) }}')">
                                <td class="px-4 py-3 font-medium">{{ $product->name }}</td>
                                <td class="px-4 py-3 text-neutral-500 dark:text-neutral-400">{{ $product->sku }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format((float) $product->quantity_on_hand) }}</td>
                                <td class="px-4 py-3 text-right">@money($product->cost_price)</td>
                                <td class="px-4 py-3 text-right">@money($product->selling_price)</td>
                                <td class="px-4 py-3 text-center">
                                    @if ((float) $product->quantity_on_hand <= 0)
                                        <flux:badge color="red" size="sm">{{ __('Out of Stock') }}</flux:badge>
                                    @elseif ($product->reorder_level && (float) $product->quantity_on_hand <= (float) $product->reorder_level)
                                        <flux:badge color="yellow" size="sm">{{ __('Low Stock') }}</flux:badge>
                                    @else
                                        <flux:badge color="green" size="sm">{{ __('In Stock') }}</flux:badge>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $products->links() }}
            </div>
        @elseif ($search)
            <x-empty-state message="No products match your search." />
        @else
            <x-empty-state
                message="No products yet. Add your first product to get started."
                action-label="Add Product"
                :action-url="route('products.create')"
            />
        @endif
</div>
