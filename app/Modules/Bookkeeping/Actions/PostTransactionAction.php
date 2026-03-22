<?php

declare(strict_types=1);

namespace Modules\Bookkeeping\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Bookkeeping\Enums\LineType;
use Modules\Bookkeeping\Exceptions\UnbalancedEntryException;
use Modules\Bookkeeping\Models\Entry;
use Modules\Bookkeeping\Services\DoubleEntryService;

class PostTransactionAction
{
    public function __construct(private readonly DoubleEntryService $doubleEntryService) {}

    /**
     * Validate and atomically post a double-entry transaction.
     *
     * @param  array<int, array{ledger_id: string, type: LineType, amount: float|string, notes?: string|null}>  $lines
     *
     * @throws UnbalancedEntryException
     */
    public function execute(
        string $date,
        string $description,
        array $lines,
        ?string $reference = null,
    ): Entry {
        $this->doubleEntryService->validateOrFail($lines);

        return DB::transaction(function () use ($date, $description, $reference, $lines): Entry {
            /** @var Entry $entry */
            $entry = Entry::create([
                'date' => $date,
                'description' => $description,
                'reference' => $reference,
                'posted_at' => now(),
            ]);

            foreach ($lines as $line) {
                $entry->lines()->create([
                    'ledger_id' => $line['ledger_id'],
                    'type' => $line['type'],
                    'amount' => $line['amount'],
                    'notes' => $line['notes'] ?? null,
                ]);
            }

            return $entry->load('lines');
        });
    }
}
