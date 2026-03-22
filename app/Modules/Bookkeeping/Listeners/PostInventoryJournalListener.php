<?php

declare(strict_types=1);

namespace Modules\Bookkeeping\Listeners;

use Modules\Bookkeeping\Actions\PostTransactionAction;
use Modules\Bookkeeping\Enums\LineType;
use Modules\Inventory\Enums\MovementType;
use Modules\Inventory\Events\StockMovementCreated;

class PostInventoryJournalListener
{
    public function __construct(private readonly PostTransactionAction $postTransactionAction) {}

    public function handle(StockMovementCreated $event): void
    {
        $movement = $event->movement;
        $ledgers = app('currentTenant')->settings['ledgers'] ?? [];

        if (empty($ledgers)) {
            return;
        }

        $totalCost = $movement->totalCost();
        $description = $movement->stockItem->name.' — '.ucfirst($movement->type->value);

        match ($movement->type) {
            MovementType::Purchase => $this->postPurchaseEntry($ledgers, $totalCost, $description),
            MovementType::Sale => $this->postSaleEntry($ledgers, $totalCost, $description),
            MovementType::Adjustment => $this->postAdjustmentEntry($ledgers, $totalCost, $movement->quantity, $description),
        };
    }

    /**
     * Purchase: Debit Inventory (asset↑), Credit Accounts Payable (liability↑).
     *
     * @param  array<string, string>  $ledgers
     */
    private function postPurchaseEntry(array $ledgers, float $totalCost, string $description): void
    {
        $this->postTransactionAction->execute(
            date: now()->toDateString(),
            description: $description,
            lines: [
                ['ledger_id' => $ledgers['inventory'], 'type' => LineType::Debit, 'amount' => $totalCost],
                ['ledger_id' => $ledgers['accounts_payable'], 'type' => LineType::Credit, 'amount' => $totalCost],
            ],
        );
    }

    /**
     * Sale: Debit COGS (expense↑), Credit Inventory (asset↓).
     *
     * @param  array<string, string>  $ledgers
     */
    private function postSaleEntry(array $ledgers, float $totalCost, string $description): void
    {
        $this->postTransactionAction->execute(
            date: now()->toDateString(),
            description: $description,
            lines: [
                ['ledger_id' => $ledgers['cogs'], 'type' => LineType::Debit, 'amount' => $totalCost],
                ['ledger_id' => $ledgers['inventory'], 'type' => LineType::Credit, 'amount' => $totalCost],
            ],
        );
    }

    /**
     * Adjustment: direction determines which side inventory sits on.
     *   Positive (found stock): Debit Inventory, Credit Operating Expenses.
     *   Negative (lost stock):  Debit Operating Expenses, Credit Inventory.
     *
     * @param  array<string, string>  $ledgers
     */
    private function postAdjustmentEntry(array $ledgers, float $totalCost, mixed $quantity, string $description): void
    {
        $isIncrease = (float) $quantity > 0;

        $this->postTransactionAction->execute(
            date: now()->toDateString(),
            description: $description,
            lines: [
                [
                    'ledger_id' => $ledgers['inventory'],
                    'type' => $isIncrease ? LineType::Debit : LineType::Credit,
                    'amount' => $totalCost,
                ],
                [
                    'ledger_id' => $ledgers['operating_expenses'],
                    'type' => $isIncrease ? LineType::Credit : LineType::Debit,
                    'amount' => $totalCost,
                ],
            ],
        );
    }
}
