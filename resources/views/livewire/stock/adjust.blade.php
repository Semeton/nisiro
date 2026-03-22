<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use Modules\Inventory\Actions\AdjustStockAction;
use Modules\Inventory\Models\StockItem;

new class extends Component {
    public string $stock_item_id = '';
    public string $direction = 'remove';
    public string $quantity = '';
    public string $notes = '';

    public function mount(): void
    {
        $this->stock_item_id = request()->query('product', '');
    }

    public function adjust(): void
    {
        $this->validate([
            'stock_item_id' => ['required', 'exists:stock_items,id'],
            'direction' => ['required', 'in:add,remove'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['required', 'string', 'max:1000'],
        ]);

        $item = StockItem::findOrFail($this->stock_item_id);
        $adjustmentQuantity = (float) $this->quantity;

        if ($this->direction === 'remove') {
            $adjustmentQuantity = -$adjustmentQuantity;
        }

        app(AdjustStockAction::class)->execute(
            item: $item,
            quantity: $adjustmentQuantity,
            notes: $this->notes,
        );

        session()->flash('status', 'Stock adjustment recorded successfully.');

        $this->redirect(route('products.show', $item), navigate: true);
    }

    public function with(): array
    {
        return ['products' => StockItem::orderBy('name')->get()];
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col">
        <x-page-heading heading="Adjust Stock" subheading="Fix stock counts after a physical count, loss, or damage" />

        <form wire:submit="adjust" class="max-w-lg space-y-6">
            <flux:select wire:model="stock_item_id" :label="__('Product')" placeholder="Select a product..." required>
                @foreach ($products as $product)
                    <flux:select.option :value="$product->id">
                        {{ $product->name }} ({{ $product->sku }}) — {{ number_format((float) $product->quantity_on_hand) }} in stock
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:radio.group wire:model="direction" :label="__('Adjustment Type')" variant="segmented">
                <flux:radio value="add" label="Add Stock" />
                <flux:radio value="remove" label="Remove Stock" />
            </flux:radio.group>

            <flux:input wire:model="quantity" :label="__('Quantity')" type="number" step="1" min="1" required />

            <flux:textarea wire:model="notes" :label="__('Reason')" :description="__('Why are you adjusting? (e.g. physical count, damaged goods)')" rows="3" required />

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">
                    {{ __('Record Adjustment') }}
                </flux:button>
                <flux:button variant="ghost" :href="route('products.index')" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
            </div>
        </form>
</div>
