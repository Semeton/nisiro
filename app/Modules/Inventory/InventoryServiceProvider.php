<?php

declare(strict_types=1);

namespace Modules\Inventory;

use Illuminate\Support\ServiceProvider;
use Modules\Inventory\Actions\AdjustStockAction;
use Modules\Inventory\Actions\PurchaseStockAction;
use Modules\Inventory\Actions\SellStockAction;
use Modules\Inventory\Services\StockQuantityService;

class InventoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(StockQuantityService::class);
        $this->app->singleton(PurchaseStockAction::class);
        $this->app->singleton(SellStockAction::class);
        $this->app->singleton(AdjustStockAction::class);
    }

    public function boot(): void {}
}
