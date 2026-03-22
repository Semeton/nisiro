<?php

declare(strict_types=1);

namespace Modules\Bookkeeping;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Bookkeeping\Actions\PostTransactionAction;
use Modules\Bookkeeping\Listeners\PostInventoryJournalListener;
use Modules\Bookkeeping\Services\AccountBalanceService;
use Modules\Bookkeeping\Services\DoubleEntryService;
use Modules\Inventory\Events\StockMovementCreated;

class BookkeepingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DoubleEntryService::class);
        $this->app->singleton(AccountBalanceService::class);
        $this->app->singleton(PostTransactionAction::class);
    }

    public function boot(): void
    {
        Event::listen(StockMovementCreated::class, PostInventoryJournalListener::class);
    }
}
