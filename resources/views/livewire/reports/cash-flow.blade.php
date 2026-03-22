<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Support\Number;
use Livewire\Volt\Component;
use Modules\Reporting\Services\CashFlowReportService;
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
        $tenant = app('currentTenant');
        $service = app(CashFlowReportService::class);

        $report = $service->generate(
            $tenant,
            Carbon::parse($this->from),
            Carbon::parse($this->to),
        );

        return ['report' => $report];
    }

    public function exportCsv()
    {
        $tenant = app('currentTenant');
        $service = app(CashFlowReportService::class);
        $exportService = app(ReportExportService::class);

        $report = $service->generate(
            $tenant,
            Carbon::parse($this->from),
            Carbon::parse($this->to),
        );

        $headers = ['Account', 'Code', 'Money In', 'Money Out', 'Net'];
        $rows = [];

        foreach ($report['items'] as $item) {
            $rows[] = [
                $item['ledger'],
                $item['code'],
                number_format($item['inflows'], 2),
                number_format($item['outflows'], 2),
                number_format($item['net'], 2),
            ];
        }

        $rows[] = [
            'Total', '',
            number_format($report['totals']['inflows'], 2),
            number_format($report['totals']['outflows'], 2),
            number_format($report['totals']['net'], 2),
        ];

        return $exportService->toCsv($headers, $rows, 'cash-flow.csv');
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col">
    <x-page-heading heading="Cash Flow" subheading="Money coming in and going out of your cash and bank accounts">
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

    {{-- Summary Cards --}}
    <div class="mb-6 grid gap-4 md:grid-cols-3">
        <x-stat-card label="Money In" :value="Number::currency($report['totals']['inflows'], 'NGN')" />
        <x-stat-card label="Money Out" :value="Number::currency($report['totals']['outflows'], 'NGN')" />
        <x-stat-card label="Net Cash Flow" :value="Number::currency($report['totals']['net'], 'NGN')" />
    </div>

    {{-- Cash Flow Table --}}
    <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-neutral-200 bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-900">
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('Account') }}</th>
                    <th class="px-4 py-3 font-medium text-right">{{ __('Money In') }}</th>
                    <th class="px-4 py-3 font-medium text-right">{{ __('Money Out') }}</th>
                    <th class="px-4 py-3 font-medium text-right">{{ __('Net') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                @forelse ($report['items'] as $item)
                    <tr wire:key="cf-{{ $item['code'] }}">
                        <td class="px-4 py-3">
                            <span class="font-medium">{{ $item['ledger'] }}</span>
                            <span class="ml-2 text-neutral-500 dark:text-neutral-400">{{ $item['code'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-green-600 dark:text-green-400">
                            @money($item['inflows'])
                        </td>
                        <td class="px-4 py-3 text-right text-red-600 dark:text-red-400">
                            @money($item['outflows'])
                        </td>
                        <td class="px-4 py-3 text-right font-medium {{ $item['net'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            @money($item['net'])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-3 text-neutral-500 dark:text-neutral-400" colspan="4">{{ __('No cash movements for this period.') }}</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="border-t border-neutral-200 bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-900">
                <tr>
                    <td class="px-4 py-3 font-bold">{{ __('Total') }}</td>
                    <td class="px-4 py-3 text-right font-bold text-green-600 dark:text-green-400">
                        @money($report['totals']['inflows'])
                    </td>
                    <td class="px-4 py-3 text-right font-bold text-red-600 dark:text-red-400">
                        @money($report['totals']['outflows'])
                    </td>
                    <td class="px-4 py-3 text-right font-bold {{ $report['totals']['net'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        @money($report['totals']['net'])
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
