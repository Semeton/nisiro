<?php

declare(strict_types=1);

namespace Modules\Reporting\Services;

use Carbon\Carbon;
use Modules\Bookkeeping\Enums\LineType;
use Modules\Bookkeeping\Models\EntryLine;
use Modules\Bookkeeping\Models\Ledger;
use Modules\System\Models\Tenant;

class CashFlowReportService
{
    /**
     * Generate a Cash Flow report for the given tenant and date range.
     *
     * Cash/Bank ledgers are identified by codes 1000 and 1010.
     * Inflows = debits to these accounts (asset accounts increase with debits).
     * Outflows = credits to these accounts.
     *
     * @return array{
     *     period: array{from: string, to: string},
     *     items: array<int, array{ledger: string, code: string, inflows: float, outflows: float, net: float}>,
     *     totals: array{inflows: float, outflows: float, net: float}
     * }
     */
    public function generate(Tenant $tenant, Carbon $from, Carbon $to): array
    {
        $cashBankLedgers = Ledger::query()
            ->where('tenant_id', $tenant->id)
            ->whereIn('code', ['1000', '1010'])
            ->get();

        $ledgerIds = $cashBankLedgers->pluck('id');

        $lines = EntryLine::query()
            ->where('entry_lines.tenant_id', $tenant->id)
            ->whereIn('ledger_id', $ledgerIds)
            ->whereHas('entry', function ($query) use ($from, $to): void {
                $query->whereNotNull('posted_at')
                    ->whereBetween('date', [$from->toDateString(), $to->toDateString()]);
            })
            ->get();

        $items = [];
        $totalInflows = 0.0;
        $totalOutflows = 0.0;

        foreach ($cashBankLedgers as $ledger) {
            $ledgerLines = $lines->where('ledger_id', $ledger->id);

            $inflows = (float) $ledgerLines->where('type', LineType::Debit)->sum('amount');
            $outflows = (float) $ledgerLines->where('type', LineType::Credit)->sum('amount');

            $items[] = [
                'ledger' => $ledger->name,
                'code' => $ledger->code,
                'inflows' => $inflows,
                'outflows' => $outflows,
                'net' => $inflows - $outflows,
            ];

            $totalInflows += $inflows;
            $totalOutflows += $outflows;
        }

        return [
            'period' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'items' => $items,
            'totals' => [
                'inflows' => $totalInflows,
                'outflows' => $totalOutflows,
                'net' => $totalInflows - $totalOutflows,
            ],
        ];
    }
}
