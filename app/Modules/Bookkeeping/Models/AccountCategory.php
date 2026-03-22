<?php

declare(strict_types=1);

namespace Modules\Bookkeeping\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Bookkeeping\Enums\AccountType;
use Modules\System\Traits\BelongsToTenant;
use Modules\System\Traits\UsesUuidPrimaryKey;

class AccountCategory extends Model
{
    use BelongsToTenant, UsesUuidPrimaryKey;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'is_system',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AccountType::class,
            'is_system' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Ledger, $this>
     */
    public function ledgers(): HasMany
    {
        return $this->hasMany(Ledger::class);
    }
}
