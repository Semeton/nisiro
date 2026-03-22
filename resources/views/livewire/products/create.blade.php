<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use Modules\Inventory\Models\StockItem;

new class extends Component {
    public string $name = '';
    public string $sku = '';
    public string $cost_price = '';
    public string $selling_price = '';
    public string $reorder_level = '';

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:stock_items,sku'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'reorder_level' => ['nullable', 'numeric', 'min:0'],
        ]);

        $tenant = app('currentTenant');

        StockItem::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'sku' => $validated['sku'],
            'cost_price' => $validated['cost_price'],
            'selling_price' => $validated['selling_price'],
            'reorder_level' => $validated['reorder_level'] ?: null,
        ]);

        session()->flash('status', 'Product added successfully.');

        $this->redirect(route('products.index'), navigate: true);
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col">
        <x-page-heading heading="Add Product" subheading="Add a new product to your stock" />

        <form wire:submit="save" class="max-w-lg space-y-6">
            <flux:input wire:model="name" :label="__('Product Name')" required autofocus />

            <flux:input wire:model="sku" :label="__('SKU')" :description="__('A unique code for this product')" required />

            <flux:input wire:model="cost_price" :label="__('Cost Price')" :description="__('What you pay for it')" type="number" step="0.01" min="0" required />

            <flux:input wire:model="selling_price" :label="__('Selling Price')" :description="__('What you sell it for')" type="number" step="0.01" min="0" required />

            <flux:input wire:model="reorder_level" :label="__('Reorder Level')" :description="__('Alert me when stock falls below this number')" type="number" step="1" min="0" />

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">
                    {{ __('Save Product') }}
                </flux:button>
                <flux:button variant="ghost" :href="route('products.index')" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
            </div>
        </form>
</div>
