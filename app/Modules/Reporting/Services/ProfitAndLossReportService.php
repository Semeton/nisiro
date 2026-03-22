<?php

declare(strict_types=1);

namespace Modules\Reporting\Services;

use Carbon\Carbon;
use Modules\Bookkeeping\Enums\AccountType;
use Modules\Bookkeeping\Enums\LineType;
use Modules\Bookkeeping\Models\EntryLine;
use Modules\System\Models\Tenant;

class ProfitAndLossReportService
{
    /**
     * Generate a Profit & Loss report for the given tenant and date range.
     *
     * @return array{
     *     period: array{from: string, to: string},
     *     revenue: array{items: array<int, array{ledger: string, code: string, amount: float}>, total: float},
     *     expenses: array{items: array<int, array{ledger: string, code: string, amount: float}>, total: float},
     *     net_profit: float
     * }
     */
    public function generate(Tenant $tenant, Carbon $from, Carbon $to): array
    {
        $lines = EntryLine::query()
            ->where('entry_lines.tenant_id', $tenant->id)
            ->whereHas('entry', function ($query) use ($from, $to): void {
                $query->whereNotNull('posted_at')
                    ->whereBetween('date', [$from->toDateString(), $to->toDateString()]);
            })
            ->with('ledger.accountCategory')
            ->get();

        $revenue = [];
        $expenses = [];

        $grouped = $lines->groupBy(fn (EntryLine $line) => $line->ledger_id);

        foreach ($grouped as $ledgerLines) {
            $ledger = $ledgerLines->first()->ledger;
            $accountType = $ledger->accountCategory->type;

            if (! in_array($accountType, [AccountType::Revenue, AccountType::Expense])) {
                continue;
            }

            $debits = $ledgerLines->where('type', LineType::Debit)->sum('amount');
            $credits = $ledgerLines->where('type', LineType::Credit)->sum('amount');

            $balance = $accountType->normalBalance() === LineType::Debit
                ? (float) ($debits - $credits)
                : (float) ($credits - $debits);

            $item = [
                'ledger' => $ledger->name,
                'code' => $ledger->code,
                'amount' => $balance,
            ];

            if ($accountType === AccountType::Revenue) {
                $revenue[] = $item;
            } else {
                $expenses[] = $item;
            }
        }

        $revenueTotal = array_sum(array_column($revenue, 'amount'));
        $expenseTotal = array_sum(array_column($expenses, 'amount'));

        return [
            'period' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'revenue' => [
                'items' => $revenue,
                'total' => $revenueTotal,
            ],
            'expenses' => [
                'items' => $expenses,
                'total' => $expenseTotal,
            ],
            'net_profit' => $revenueTotal - $expenseTotal,
        ];
    }
}
