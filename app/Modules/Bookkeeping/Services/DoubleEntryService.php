<?php

declare(strict_types=1);

namespace Modules\Bookkeeping\Services;

use Modules\Bookkeeping\Enums\LineType;
use Modules\Bookkeeping\Exceptions\UnbalancedEntryException;

class DoubleEntryService
{
    /**
     * Returns true if the given lines are balanced (debits == credits).
     *
     * @param  array<int, array{type: LineType, amount: float|string}>  $lines
     */
    public function isBalanced(array $lines): bool
    {
        $debits = 0.0;
        $credits = 0.0;

        foreach ($lines as $line) {
            if ($line['type'] === LineType::Debit) {
                $debits += (float) $line['amount'];
            } else {
                $credits += (float) $line['amount'];
            }
        }

        return abs($debits - $credits) < 0.001;
    }

    /**
     * Validates that lines are balanced; throws if not.
     *
     * @param  array<int, array{type: LineType, amount: float|string}>  $lines
     *
     * @throws UnbalancedEntryException
     */
    public function validateOrFail(array $lines): void
    {
        $debits = 0.0;
        $credits = 0.0;

        foreach ($lines as $line) {
            if ($line['type'] === LineType::Debit) {
                $debits += (float) $line['amount'];
            } else {
                $credits += (float) $line['amount'];
            }
        }

        if (abs($debits - $credits) >= 0.001) {
            throw new UnbalancedEntryException($debits, $credits);
        }
    }
}
