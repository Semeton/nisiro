<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Support\Number;
use Livewire\Volt\Component;
use Modules\Reporting\Services\ReportExportService;
use Modules\Reporting\Services\TaxVisibilityReportService;

new class extends Component {
    public string $from;

    public string $to;

    public function mount(): void
    {
        $this->from = now()->startOfYear()->toDateString();
        $this->to = now()->toDateString();
    }

    public function with(): array
    {
        $tenant = app('currentTenant');
        $service = app(TaxVisibilityReportService::class);

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
        $service = app(TaxVisibilityReportService::class);
        $exportService = app(ReportExportService::class);

        $report = $service->generate(
            $tenant,
            Carbon::parse($this->from),
            Carbon::parse($this->to),
        );

        $headers = ['Tax Obligation', 'Status', 'Reason'];
        $rows = [];

        foreach ($report['obligations'] as $obligation) {
            $rows[] = [
                $obligation['name'],
                $obligation['applicable'] ? 'Applicable' : 'Not Applicable',
                $obligation['reason'],
            ];
        }

        return $exportService->toCsv($headers, $rows, 'tax-visibility.csv');
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col">
    <x-page-heading heading="Tax Visibility" subheading="Overview of potential tax obligations based on your business activity">
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

    {{-- Financial Summary --}}
    <div class="mb-6 grid gap-4 md:grid-cols-2">
        <x-stat-card label="Total Revenue" :value="Number::currency($report['revenue'], 'NGN')" />
        <x-stat-card label="Net Profit" :value="Number::currency($report['net_profit'], 'NGN')" />
    </div>

    <flux:callout icon="information-circle" class="mb-6">
        <flux:callout.heading>{{ __('Disclaimer') }}</flux:callout.heading>
        <flux:callout.text>
            {{ __('This report provides visibility into potential tax obligations. It is not tax advice. Please consult a qualified tax professional for compliance guidance.') }}
        </flux:callout.text>
    </flux:callout>

    {{-- Obligations --}}
    <div class="space-y-4">
        @foreach ($report['obligations'] as $obligation)
            <div wire:key="obl-{{ Str::slug($obligation['name']) }}" class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <div class="flex items-center justify-between px-4 py-4">
                    <div>
                        <flux:heading size="sm">{{ $obligation['name'] }}</flux:heading>
                        <flux:text class="mt-1">{{ $obligation['reason'] }}</flux:text>
                    </div>
                    <div>
                        @if ($obligation['applicable'])
                            <flux:badge color="yellow">{{ __('May Apply') }}</flux:badge>
                        @else
                            <flux:badge color="green">{{ __('Not Applicable') }}</flux:badge>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
