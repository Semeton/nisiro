<?php

declare(strict_types=1);

use Illuminate\Support\Number;
use Livewire\Volt\Component;
use Modules\Bookkeeping\Enums\AccountType;
use Modules\Bookkeeping\Models\AccountCategory;
use Modules\Bookkeeping\Services\AccountBalanceService;

new class extends Component {
    public function friendlyLabel(AccountType $type): string
    {
        return match ($type) {
            AccountType::Assets => 'What You Own',
            AccountType::Liabilities => 'What You Owe',
            AccountType::Equity => "Owner's Investment",
            AccountType::Revenue => 'Income',
            AccountType::Expense => 'Expenses',
        };
    }

    public function with(): array
    {
        $balanceService = app(AccountBalanceService::class);

        $categories = AccountCategory::with('ledgers.accountCategory')
            ->orderByRaw("FIELD(type, 'assets', 'liabilities', 'equity', 'revenue', 'expense')")
            ->get();

        $balances = [];
        foreach ($categories as $category) {
            foreach ($category->ledgers as $ledger) {
                $balances[$ledger->id] = $balanceService->balanceForLedger($ledger);
            }
        }

        return [
            'categories' => $categories,
            'balances' => $balances,
        ];
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col">
        <x-page-heading heading="Accounts" subheading="Your business accounts and their balances" />

        <div class="space-y-6">
            @foreach ($categories as $category)
                <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <div class="border-b border-neutral-200 bg-neutral-50 px-4 py-3 dark:border-neutral-700 dark:bg-zinc-900">
                        <flux:heading size="sm">{{ $this->friendlyLabel($category->type) }}</flux:heading>
                    </div>
                    <table class="w-full text-left text-sm">
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                            @foreach ($category->ledgers as $ledger)
                                @php $balance = $balances[$ledger->id] ?? 0.0; @endphp
                                <tr wire:key="ledger-{{ $ledger->id }}">
                                    <td class="px-4 py-3 text-neutral-500 dark:text-neutral-400 w-20">{{ $ledger->code }}</td>
                                    <td class="px-4 py-3 font-medium">{{ $ledger->name }}</td>
                                    <td class="px-4 py-3 text-right font-medium {{ $balance >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ Number::currency($balance, 'NGN') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t border-neutral-200 bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-900">
                            <tr>
                                <td class="px-4 py-3" colspan="2">
                                    <flux:text class="font-medium">{{ __('Total') }}</flux:text>
                                </td>
                                <td class="px-4 py-3 text-right font-bold">
                                    @php $categoryTotal = $category->ledgers->sum(fn ($l) => $balances[$l->id] ?? 0.0); @endphp
                                    {{ Number::currency($categoryTotal, 'NGN') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endforeach
        </div>
</div>
