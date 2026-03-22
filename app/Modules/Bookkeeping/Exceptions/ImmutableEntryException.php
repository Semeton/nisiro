<?php

declare(strict_types=1);

namespace Modules\Bookkeeping\Exceptions;

use RuntimeException;

class ImmutableEntryException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Cannot modify or delete a posted entry.');
    }
}
