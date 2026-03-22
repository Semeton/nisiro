<?php

declare(strict_types=1);

namespace Modules\Inventory\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Inventory\Enums\MovementType;
use Modules\Inventory\Events\StockMovementCreated;
use Modules\Inventory\Models\StockItem;
use Modules\Inventory\Models\StockMovement;

class AdjustStockAction
{
    /**
     * Record a manual stock adjustment (e.g. loss, theft, count correction).
     *
     * Pass a positive quantity to increase stock, negative to decrease.
     */
    public function execute(
        StockItem $item,
        float $quantity,
        ?string $notes = null,
    ): StockMovement {
        return DB::transaction(function () use ($item, $quantity, $notes): StockMovement {
            $unitCost = (float) $item->cost_price;

            $item->increment('quantity_on_hand', $quantity);

            $movement = $item->movements()->create([
                'tenant_id' => $item->tenant_id,
                'type' => MovementType::Adjustment,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'notes' => $notes,
            ]);

            StockMovementCreated::dispatch($movement);

            return $movement;
        });
    }
}
