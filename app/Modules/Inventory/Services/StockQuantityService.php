<?php

declare(strict_types=1);

namespace Modules\Inventory\Services;

use Modules\Inventory\Models\StockItem;

class StockQuantityService
{
    /**
     * Returns the current quantity on hand for the given item.
     * Uses the cached value on the model for performance.
     */
    public function quantityOnHand(StockItem $item): float
    {
        return (float) $item->quantity_on_hand;
    }

    /**
     * Recomputes quantity on hand from the movements log.
     * Useful for reconciliation or auditing.
     */
    public function recomputeFromMovements(StockItem $item): float
    {
        return (float) $item->movements()->sum('quantity');
    }
}
