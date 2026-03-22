<?php

declare(strict_types=1);

namespace Modules\Inventory\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(string $itemName, float $available, float $requested)
    {
        parent::__construct(
            sprintf(
                "Insufficient stock for '%s': %.2f available, %.2f requested.",
                $itemName,
                $available,
                $requested,
            ),
        );
    }
}
