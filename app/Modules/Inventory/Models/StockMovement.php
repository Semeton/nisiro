<?php

declare(strict_types=1);

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Inventory\Enums\MovementType;
use Modules\System\Traits\BelongsToTenant;
use Modules\System\Traits\UsesUuidPrimaryKey;

class StockMovement extends Model
{
    use BelongsToTenant, UsesUuidPrimaryKey;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'stock_item_id',
        'type',
        'quantity',
        'unit_cost',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => MovementType::class,
            'quantity' => 'decimal:2',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function totalCost(): float
    {
        return abs((float) $this->quantity) * (float) $this->unit_cost;
    }

    /**
     * @return BelongsTo<StockItem, $this>
     */
    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }
}
