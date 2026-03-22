<?php

declare(strict_types=1);

namespace Modules\Bookkeeping\Enums;

enum AccountType: string
{
    case Assets = 'assets';
    case Liabilities = 'liabilities';
    case Equity = 'equity';
    case Revenue = 'revenue';
    case Expense = 'expense';

    /**
     * Returns the normal (natural) balance side for this account type.
     * Assets and Expenses increase with debits; the rest increase with credits.
     */
    public function normalBalance(): LineType
    {
        return match ($this) {
            self::Assets, self::Expense => LineType::Debit,
            self::Liabilities, self::Equity, self::Revenue => LineType::Credit,
        };
    }
}
