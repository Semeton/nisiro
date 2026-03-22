<?php

declare(strict_types=1);

namespace Modules\Reporting\Enums;

enum ReportType: string
{
    case ProfitAndLoss = 'profit_and_loss';
    case CashFlow = 'cash_flow';
    case TaxVisibility = 'tax_visibility';
}
