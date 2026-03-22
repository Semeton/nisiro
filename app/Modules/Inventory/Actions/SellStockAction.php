<?php

declare(strict_types=1);

namespace Modules\Inventory\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Inventory\Enums\MovementType;
use Modules\Inventory\Events\StockMovementCreated;
use Modules\Inventory\Exceptions\InsufficientStockException;
use Modules\Inventory\Models\StockItem;
use Modules\Inventory\Models\StockMovement;

class SellStockAction
{
    /**
     * Record a stock sale at the current weighted average cost and fire the
     * event so the Bookkeeping module can post the COGS journal entry.
     *
     * @throws InsufficientStockException
     */
    public function execute(
        StockItem $item,
        float $quantity,
        ?string $notes = null,
    ): StockMovement {
        if ((float) $item->quantity_on_hand < $quantity) {
            throw new InsufficientStockException(
                $item->name,
                (float) $item->quantity_on_hand,
                $quantity,
            );
        }

        return DB::transaction(function () use ($item, $quantity, $notes): StockMovement {
            $unitCost = (float) $item->cost_price;

            $item->decrement('quantity_on_hand', $quantity);

            // Negative quantity signals stock out.
            $movement = $item->movements()->create([
                'tenant_id' => $item->tenant_id,
                'type' => MovementType::Sale,
                'quantity' => -$quantity,
                'unit_cost' => $unitCost,
                'notes' => $notes,
            ]);

            StockMovementCreated::dispatch($movement);

            return $movement;
        });
    }
}
