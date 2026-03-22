<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Support\Number;
use Livewire\Volt\Component;
use Modules\Reporting\Services\ProfitAndLossReportService;
use Modules\Reporting\Services\ReportExportService;

new class extends Component {
    public string $from;

    public string $to;

    public function mount(): void
    {
        $this->from = now()->startOfMonth()->toDateString();
        $this->to = now()->toDateString();
    }

    public function with(): array
    {
        if (! app()->bound('currentTenant')) {
            return ['report' => ['period' => ['from' => $this->from, 'to' => $this->to], 'revenue' => ['items' => [], 'total' => 0], 'expenses' => ['items' => [], 'total' => 0], 'net_profit' => 0]];
        }

        $tenant = app('currentTenant');
        $service = app(ProfitAndLossReportService::class);

        $report = $service->generate(
            $tenant,
            Carbon::parse($this->from),
            Carbon::parse($this->to),
        );

        return ['report' => $report];
    }

    public function exportCsv()
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        if (! $tenant) {
            return;
        }
        $service = app(ProfitAndLossReportService::class);
        $exportService = app(ReportExportService::class);

        $report = $service->generate(
            $tenant,
            Carbon::parse($this->from),
            Carbon::parse($this->to),
        );

        $headers = ['Category', 'Ledger', 'Code', 'Amount'];
        $rows = [];

        foreach ($report['revenue']['items'] as $item) {
            $rows[] = ['Revenue', $item['ledger'], $item['code'], number_format($item['amount'], 2)];
        }
        $rows[] = ['', '', 'Total Revenue', number_format($report['revenue']['total'], 2)];

        foreach ($report['expenses']['items'] as $item) {
            $rows[] = ['Expense', $item['ledger'], $item['code'], number_format($item['amount'], 2)];
        }
        $rows[] = ['', '', 'Total Expenses', number_format($report['expenses']['total'], 2)];
        $rows[] = ['', '', 'Net Profit', number_format($report['net_profit'], 2)];

        return $exportService->toCsv($headers, $rows, 'profit-and-loss.csv');
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col">
    <x-page-heading heading="Profit & Loss" subheading="Income and expenses for the selected period">
        <x-slot:actions>
            <flux:button wire:click="exportCsv" icon="arrow-down-tray" size="sm">
                {{ __('Export CSV') }}
            </flux:button>
        </x-slot:actions>
    </x-page-heading>

    {{-- Date Range Filter --}}
    <div class="mb-6 flex flex-wrap items-end gap-4">
        <flux:field>
            <flux:label>{{ __('From') }}</flux:label>
            <flux:input type="date" wire:model.live="from" />
        </flux:field>
        <flux:field>
            <flux:label>{{ __('To') }}</flux:label>
            <flux:input type="date" wire:model.live="to" />
        </flux:field>
    </div>

    {{-- Revenue Section --}}
    <div class="mb-6 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <div class="border-b border-neutral-200 bg-neutral-50 px-4 py-3 dark:border-neutral-700 dark:bg-zinc-900">
            <flux:heading size="sm">{{ __('Income') }}</flux:heading>
        </div>
        <table class="w-full text-left text-sm">
            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                @forelse ($report['revenue']['items'] as $item)
                    <tr wire:key="rev-{{ $item['code'] }}">
                        <td class="w-20 px-4 py-3 text-neutral-500 dark:text-neutral-400">{{ $item['code'] }}</td>
                        <td class="px-4 py-3">{{ $item['ledger'] }}</td>
                        <td class="px-4 py-3 text-right font-medium text-green-600 dark:text-green-400">
                            @money($item['amount'])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-3 text-neutral-500 dark:text-neutral-400" colspan="3">{{ __('No income recorded for this period.') }}</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="border-t border-neutral-200 bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-900">
                <tr>
                    <td class="px-4 py-3 font-medium" colspan="2">{{ __('Total Income') }}</td>
                    <td class="px-4 py-3 text-right font-bold text-green-600 dark:text-green-400">
                        @money($report['revenue']['total'])
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Expenses Section --}}
    <div class="mb-6 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <div class="border-b border-neutral-200 bg-neutral-50 px-4 py-3 dark:border-neutral-700 dark:bg-zinc-900">
            <flux:heading size="sm">{{ __('Expenses') }}</flux:heading>
        </div>
        <table class="w-full text-left text-sm">
            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                @forelse ($report['expenses']['items'] as $item)
                    <tr wire:key="exp-{{ $item['code'] }}">
                        <td class="w-20 px-4 py-3 text-neutral-500 dark:text-neutral-400">{{ $item['code'] }}</td>
                        <td class="px-4 py-3">{{ $item['ledger'] }}</td>
                        <td class="px-4 py-3 text-right font-medium text-red-600 dark:text-red-400">
                            @money($item['amount'])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-3 text-neutral-500 dark:text-neutral-400" colspan="3">{{ __('No expenses recorded for this period.') }}</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="border-t border-neutral-200 bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-900">
                <tr>
                    <td class="px-4 py-3 font-medium" colspan="2">{{ __('Total Expenses') }}</td>
                    <td class="px-4 py-3 text-right font-bold text-red-600 dark:text-red-400">
                        @money($report['expenses']['total'])
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Net Profit --}}
    <div class="overflow-hidden rounded-xl border-2 {{ $report['net_profit'] >= 0 ? 'border-green-200 dark:border-green-800' : 'border-red-200 dark:border-red-800' }}">
        <div class="flex items-center justify-between px-4 py-4 {{ $report['net_profit'] >= 0 ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20' }}">
            <flux:heading size="lg">{{ __('Net Profit') }}</flux:heading>
            <span class="text-xl font-bold {{ $report['net_profit'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                @money($report['net_profit'])
            </span>
        </div>
    </div>
</div>
