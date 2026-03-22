<?php

declare(strict_types=1);

namespace Modules\Bookkeeping\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Bookkeeping\Enums\LineType;
use Modules\Bookkeeping\Exceptions\ImmutableEntryException;
use Modules\System\Traits\BelongsToTenant;
use Modules\System\Traits\UsesUuidPrimaryKey;

class EntryLine extends Model
{
    use BelongsToTenant, UsesUuidPrimaryKey;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'entry_id',
        'ledger_id',
        'type',
        'amount',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => LineType::class,
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Guard against mutations to lines belonging to posted entries.
     */
    protected static function booted(): void
    {
        static::updating(function (self $line): void {
            if ($line->entry?->isPosted()) {
                throw new ImmutableEntryException;
            }
        });

        static::deleting(function (self $line): void {
            if ($line->entry?->isPosted()) {
                throw new ImmutableEntryException;
            }
        });
    }

    /**
     * @return BelongsTo<Entry, $this>
     */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }

    /**
     * @return BelongsTo<Ledger, $this>
     */
    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }
}
