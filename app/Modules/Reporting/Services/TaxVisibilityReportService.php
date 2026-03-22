<?php

declare(strict_types=1);

namespace Modules\Reporting\Services;

use Carbon\Carbon;
use Modules\RulesEngine\Services\RuleEvaluatorService;
use Modules\System\Models\Tenant;

class TaxVisibilityReportService
{
    public function __construct(
        private readonly ProfitAndLossReportService $profitAndLossService,
        private readonly RuleEvaluatorService $ruleEvaluator,
    ) {}

    /**
     * Generate a Tax Visibility report for the given tenant and date range.
     *
     * Uses the Rules Engine to evaluate which tax obligations are applicable
     * based on the tenant's financial data for the period.
     *
     * @return array{
     *     period: array{from: string, to: string},
     *     revenue: float,
     *     net_profit: float,
     *     obligations: array<int, array{name: string, applicable: bool, reason: string}>
     * }
     */
    public function generate(Tenant $tenant, Carbon $from, Carbon $to): array
    {
        $pnl = $this->profitAndLossService->generate($tenant, $from, $to);

        $context = [
            'revenue' => $pnl['revenue']['total'],
            'expenses' => $pnl['expenses']['total'],
            'net_profit' => $pnl['net_profit'],
        ];

        $obligations = [];

        foreach ($this->defaultRules() as $ruleDef) {
            $result = $this->ruleEvaluator->evaluate($context, [$ruleDef['rule']]);
            $applicable = ! empty($result) && ($result['applicable'] ?? false);

            $obligations[] = [
                'name' => $ruleDef['name'],
                'applicable' => $applicable,
                'reason' => $applicable
                    ? $ruleDef['applicable_reason']
                    : $ruleDef['not_applicable_reason'],
            ];
        }

        return [
            'period' => $pnl['period'],
            'revenue' => $pnl['revenue']['total'],
            'net_profit' => $pnl['net_profit'],
            'obligations' => $obligations,
        ];
    }

    /**
     * Default tax visibility rules for Nigerian SMEs.
     *
     * @return array<int, array{name: string, rule: array<string, mixed>, applicable_reason: string, not_applicable_reason: string}>
     */
    private function defaultRules(): array
    {
        return [
            [
                'name' => 'VAT Registration',
                'rule' => [
                    'conditions' => [
                        ['field' => 'revenue', 'operator' => 'greater_than', 'value' => 25_000_000],
                    ],
                    'match' => 'all',
                    'actions' => ['applicable' => true],
                ],
                'applicable_reason' => 'Revenue exceeds NGN 25,000,000 threshold',
                'not_applicable_reason' => 'Revenue is below NGN 25,000,000 threshold',
            ],
            [
                'name' => 'Company Income Tax',
                'rule' => [
                    'conditions' => [
                        ['field' => 'net_profit', 'operator' => 'greater_than', 'value' => 0],
                    ],
                    'match' => 'all',
                    'actions' => ['applicable' => true],
                ],
                'applicable_reason' => 'Business has taxable profit for the period',
                'not_applicable_reason' => 'No taxable profit for the period',
            ],
            [
                'name' => 'Withholding Tax',
                'rule' => [
                    'conditions' => [
                        ['field' => 'expenses', 'operator' => 'greater_than', 'value' => 0],
                    ],
                    'match' => 'all',
                    'actions' => ['applicable' => true],
                ],
                'applicable_reason' => 'Business has deductible expenses — WHT may apply on qualifying payments',
                'not_applicable_reason' => 'No expenses recorded for the period',
            ],
        ];
    }
}
