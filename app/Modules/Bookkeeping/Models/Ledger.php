<?php

declare(strict_types=1);

namespace Modules\Bookkeeping\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\System\Traits\BelongsToTenant;
use Modules\System\Traits\UsesUuidPrimaryKey;

class Ledger extends Model
{
    use BelongsToTenant, UsesUuidPrimaryKey;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'account_category_id',
        'name',
        'code',
        'description',
    ];

    /**
     * @return BelongsTo<AccountCategory, $this>
     */
    public function accountCategory(): BelongsTo
    {
        return $this->belongsTo(AccountCategory::class);
    }

    /**
     * @return HasMany<EntryLine, $this>
     */
    public function entryLines(): HasMany
    {
        return $this->hasMany(EntryLine::class);
    }
}
