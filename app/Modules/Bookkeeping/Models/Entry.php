<?php

declare(strict_types=1);

namespace Modules\Bookkeeping\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Bookkeeping\Exceptions\ImmutableEntryException;
use Modules\System\Traits\BelongsToTenant;
use Modules\System\Traits\UsesUuidPrimaryKey;

class Entry extends Model
{
    use BelongsToTenant, UsesUuidPrimaryKey;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'date',
        'description',
        'reference',
        'posted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'posted_at' => 'datetime',
        ];
    }

    /**
     * Guard against mutations to posted entries.
     */
    protected static function booted(): void
    {
        static::updating(function (self $entry): void {
            if ($entry->getOriginal('posted_at') !== null) {
                throw new ImmutableEntryException;
            }
        });

        static::deleting(function (self $entry): void {
            if ($entry->posted_at !== null) {
                throw new ImmutableEntryException;
            }
        });
    }

    public function isPosted(): bool
    {
        return $this->posted_at !== null;
    }

    /**
     * @return HasMany<EntryLine, $this>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(EntryLine::class);
    }
}
