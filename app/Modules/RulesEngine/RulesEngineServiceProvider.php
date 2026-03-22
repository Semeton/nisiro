<?php

declare(strict_types=1);

namespace Modules\RulesEngine;

use Illuminate\Support\ServiceProvider;
use Modules\RulesEngine\Services\RuleEvaluatorService;

class RulesEngineServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RuleEvaluatorService::class);
    }

    public function boot(): void
    {
        //
    }
}
