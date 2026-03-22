<?php

declare(strict_types=1);

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\System\Traits\BelongsToTenant;
use Modules\System\Traits\UsesUuidPrimaryKey;

class StockItem extends Model
{
    use BelongsToTenant, UsesUuidPrimaryKey;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'sku',
        'cost_price',
        'selling_price',
        'quantity_on_hand',
        'reorder_level',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'quantity_on_hand' => 'decimal:2',
            'reorder_level' => 'decimal:2',
        ];
    }

    /**
     * @return HasMany<StockBatch, $this>
     */
    public function batches(): HasMany
    {
        return $this->hasMany(StockBatch::class);
    }

    /**
     * @return HasMany<StockMovement, $this>
     */
    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
