<?php

declare(strict_types=1);

namespace Modules\Reporting;

use Illuminate\Support\ServiceProvider;
use Modules\Reporting\Services\CashFlowReportService;
use Modules\Reporting\Services\ProfitAndLossReportService;
use Modules\Reporting\Services\ReportExportService;
use Modules\Reporting\Services\TaxVisibilityReportService;

class ReportingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ProfitAndLossReportService::class);
        $this->app->singleton(CashFlowReportService::class);
        $this->app->singleton(TaxVisibilityReportService::class);
        $this->app->singleton(ReportExportService::class);
    }

    public function boot(): void
    {
        //
    }
}
