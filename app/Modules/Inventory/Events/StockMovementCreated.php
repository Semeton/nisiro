<?php

declare(strict_types=1);

namespace Modules\Inventory\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Inventory\Models\StockMovement;

class StockMovementCreated
{
    use Dispatchable;

    public function __construct(public readonly StockMovement $movement) {}
}
