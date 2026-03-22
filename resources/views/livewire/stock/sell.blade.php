<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use Modules\Inventory\Actions\SellStockAction;
use Modules\Inventory\Exceptions\InsufficientStockException;
use Modules\Inventory\Models\StockItem;

new class extends Component {
    public string $stock_item_id = '';
    public string $quantity = '';
    public string $notes = '';

    public function mount(): void
    {
        $this->stock_item_id = request()->query('product', '');
    }

    public function sell(): void
    {
        $this->validate([
            'stock_item_id' => ['required', 'exists:stock_items,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $item = StockItem::findOrFail($this->stock_item_id);

        try {
            app(SellStockAction::class)->execute(
                item: $item,
                quantity: (float) $this->quantity,
                notes: $this->notes ?: null,
            );
        } catch (InsufficientStockException) {
            $this->addError('quantity', 'You only have ' . number_format((float) $item->quantity_on_hand) . ' units of this product.');

            return;
        }

        session()->flash('status', 'Sale recorded successfully.');

        $this->redirect(route('products.show', $item), navigate: true);
    }

    public function with(): array
    {
        return ['products' => StockItem::orderBy('name')->get()];
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col">
        <x-page-heading heading="Record Sale" subheading="Record stock you sold" />

        <form wire:submit="sell" class="max-w-lg space-y-6">
            <flux:select wire:model="stock_item_id" :label="__('Product')" placeholder="Select a product..." required>
                @foreach ($products as $product)
                    <flux:select.option :value="$product->id">
                        {{ $product->name }} ({{ $product->sku }}) — {{ number_format((float) $product->quantity_on_hand) }} in stock
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:input wire:model="quantity" :label="__('Quantity')" :description="__('How many did you sell?')" type="number" step="1" min="1" required />

            <flux:textarea wire:model="notes" :label="__('Notes')" :description="__('Optional')" rows="3" />

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">
                    {{ __('Record Sale') }}
                </flux:button>
                <flux:button variant="ghost" :href="route('products.index')" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
            </div>
        </form>
</div>
