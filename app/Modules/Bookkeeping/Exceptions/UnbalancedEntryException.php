<?php

declare(strict_types=1);

namespace Modules\Bookkeeping\Exceptions;

use RuntimeException;

class UnbalancedEntryException extends RuntimeException
{
    public function __construct(float $totalDebits, float $totalCredits)
    {
        parent::__construct(
            sprintf(
                'Entry is unbalanced: debits (%.2f) do not equal credits (%.2f).',
                $totalDebits,
                $totalCredits,
            ),
        );
    }
}
