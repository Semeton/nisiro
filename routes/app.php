<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware(['auth', 'verified'])->group(function () {
    // Products
    Volt::route('products', 'products.index')->name('products.index');
    Volt::route('products/create', 'products.create')->name('products.create');
    Volt::route('products/{stockItem}', 'products.show')->name('products.show');

    // Stock operations
    Volt::route('stock/purchase', 'stock.purchase')->name('stock.purchase');
    Volt::route('stock/sell', 'stock.sell')->name('stock.sell');
    Volt::route('stock/adjust', 'stock.adjust')->name('stock.adjust');
    Volt::route('stock/history', 'stock.history')->name('stock.history');

    // Accounts & Transactions
    Volt::route('accounts', 'accounts.index')->name('accounts.index');
    Volt::route('transactions', 'transactions.index')->name('transactions.index');
    Volt::route('transactions/create', 'transactions.create')->name('transactions.create');
    Volt::route('transactions/{entry}', 'transactions.show')->name('transactions.show');

    // Reports
    Volt::route('reports/profit-and-loss', 'reports.profit-and-loss')->name('reports.profit-and-loss');
    Volt::route('reports/cash-flow', 'reports.cash-flow')->name('reports.cash-flow');
    Volt::route('reports/tax-visibility', 'reports.tax-visibility')->name('reports.tax-visibility');
});
