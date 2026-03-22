<?php

declare(strict_types=1);

namespace Modules\Bookkeeping\Services;

use Modules\Bookkeeping\Enums\LineType;
use Modules\Bookkeeping\Models\Ledger;

class AccountBalanceService
{
    /**
     * Compute the running balance for a ledger account.
     *
     * For accounts with a normal debit balance (Assets, Expense):
     *   balance = sum(debits) - sum(credits)
     *
     * For accounts with a normal credit balance (Liabilities, Equity, Revenue):
     *   balance = sum(credits) - sum(debits)
     *
     * A positive result means the account holds value on its normal side.
     */
    public function balanceForLedger(Ledger $ledger): float
    {
        $lines = $ledger->entryLines()
            ->whereHas('entry', fn ($q) => $q->whereNotNull('posted_at'))
            ->get();

        $debits = $lines->where('type', LineType::Debit)->sum('amount');
        $credits = $lines->where('type', LineType::Credit)->sum('amount');

        $normalBalance = $ledger->accountCategory->type->normalBalance();

        return $normalBalance === LineType::Debit
            ? (float) ($debits - $credits)
            : (float) ($credits - $debits);
    }
}
