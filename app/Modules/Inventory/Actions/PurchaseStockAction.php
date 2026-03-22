<?php

declare(strict_types=1);

namespace Modules\Inventory\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Inventory\Enums\MovementType;
use Modules\Inventory\Events\StockMovementCreated;
use Modules\Inventory\Models\StockItem;
use Modules\Inventory\Models\StockMovement;

class PurchaseStockAction
{
    /**
     * Record a stock purchase, update weighted average cost, and fire the event
     * so the Bookkeeping module can post the corresponding journal entry.
     */
    public function execute(
        StockItem $item,
        float $quantity,
        float $unitCost,
        ?string $notes = null,
    ): StockMovement {
        return DB::transaction(function () use ($item, $quantity, $unitCost, $notes): StockMovement {
            // Recalculate weighted average cost before updating quantity.
            $oldQuantity = (float) $item->quantity_on_hand;
            $oldCost = (float) $item->cost_price;

            $newQuantity = $oldQuantity + $quantity;
            $newCost = $newQuantity > 0
                ? (($oldQuantity * $oldCost) + ($quantity * $unitCost)) / $newQuantity
                : $unitCost;

            $item->update([
                'quantity_on_hand' => $newQuantity,
                'cost_price' => round($newCost, 2),
            ]);

            $item->batches()->create([
                'tenant_id' => $item->tenant_id,
                'quantity' => $quantity,
                'quantity_remaining' => $quantity,
                'unit_cost' => $unitCost,
            ]);

            $movement = $item->movements()->create([
                'tenant_id' => $item->tenant_id,
                'type' => MovementType::Purchase,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'notes' => $notes,
            ]);

            StockMovementCreated::dispatch($movement);

            return $movement;
        });
    }
}
