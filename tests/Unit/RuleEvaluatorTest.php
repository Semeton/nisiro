<?php

declare(strict_types=1);

namespace Tests\Unit;

use Modules\RulesEngine\Services\RuleEvaluatorService;
use PHPUnit\Framework\TestCase;

class RuleEvaluatorTest extends TestCase
{
    private RuleEvaluatorService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new RuleEvaluatorService;
    }

    public function test_evaluates_greater_than_condition(): void
    {
        $context = ['revenue' => 50000];
        $rules = [
            [
                'conditions' => [
                    ['field' => 'revenue', 'operator' => 'greater_than', 'value' => 25000],
                ],
                'actions' => ['vat_applicable' => true],
            ],
        ];

        $result = $this->service->evaluate($context, $rules);

        $this->assertTrue($result['vat_applicable']);
    }

    public function test_evaluates_less_than_condition(): void
    {
        $context = ['revenue' => 10000];
        $rules = [
            [
                'conditions' => [
                    ['field' => 'revenue', 'operator' => 'greater_than', 'value' => 25000],
                ],
                'actions' => ['vat_applicable' => true],
            ],
        ];

        $result = $this->service->evaluate($context, $rules);

        $this->assertEmpty($result);
    }

    public function test_evaluates_multiple_conditions_with_all_match(): void
    {
        $context = ['revenue' => 50000, 'state' => 'Lagos'];
        $rules = [
            [
                'conditions' => [
                    ['field' => 'revenue', 'operator' => 'greater_than', 'value' => 25000],
                    ['field' => 'state', 'operator' => 'equals', 'value' => 'Lagos'],
                ],
                'match' => 'all',
                'actions' => ['state_tax' => true],
            ],
        ];

        $result = $this->service->evaluate($context, $rules);

        $this->assertTrue($result['state_tax']);
    }

    public function test_all_match_fails_when_one_condition_fails(): void
    {
        $context = ['revenue' => 50000, 'state' => 'Abuja'];
        $rules = [
            [
                'conditions' => [
                    ['field' => 'revenue', 'operator' => 'greater_than', 'value' => 25000],
                    ['field' => 'state', 'operator' => 'equals', 'value' => 'Lagos'],
                ],
                'match' => 'all',
                'actions' => ['state_tax' => true],
            ],
        ];

        $result = $this->service->evaluate($context, $rules);

        $this->assertEmpty($result);
    }

    public function test_evaluates_multiple_conditions_with_any_match(): void
    {
        $context = ['revenue' => 50000, 'state' => 'Abuja'];
        $rules = [
            [
                'conditions' => [
                    ['field' => 'revenue', 'operator' => 'greater_than', 'value' => 25000],
                    ['field' => 'state', 'operator' => 'equals', 'value' => 'Lagos'],
                ],
                'match' => 'any',
                'actions' => ['tax_review' => true],
            ],
        ];

        $result = $this->service->evaluate($context, $rules);

        $this->assertTrue($result['tax_review']);
    }

    public function test_returns_empty_when_no_rules_match(): void
    {
        $context = ['revenue' => 1000];
        $rules = [
            [
                'conditions' => [
                    ['field' => 'revenue', 'operator' => 'greater_than', 'value' => 25000],
                ],
                'actions' => ['vat_applicable' => true],
            ],
        ];

        $result = $this->service->evaluate($context, $rules);

        $this->assertEmpty($result);
    }

    public function test_evaluate_first_returns_only_first_match(): void
    {
        $context = ['revenue' => 50000];
        $rules = [
            [
                'conditions' => [
                    ['field' => 'revenue', 'operator' => 'greater_than', 'value' => 25000],
                ],
                'actions' => ['tier' => 'high'],
            ],
            [
                'conditions' => [
                    ['field' => 'revenue', 'operator' => 'greater_than', 'value' => 10000],
                ],
                'actions' => ['tier' => 'medium'],
            ],
        ];

        $result = $this->service->evaluateFirst($context, $rules);

        $this->assertSame('high', $result['tier']);
    }

    public function test_evaluate_first_returns_null_when_no_match(): void
    {
        $context = ['revenue' => 100];
        $rules = [
            [
                'conditions' => [
                    ['field' => 'revenue', 'operator' => 'greater_than', 'value' => 25000],
                ],
                'actions' => ['tier' => 'high'],
            ],
        ];

        $result = $this->service->evaluateFirst($context, $rules);

        $this->assertNull($result);
    }

    public function test_supports_in_operator(): void
    {
        $context = ['state' => 'Lagos'];
        $rules = [
            [
                'conditions' => [
                    ['field' => 'state', 'operator' => 'in', 'value' => ['Lagos', 'Abuja', 'Rivers']],
                ],
                'actions' => ['special_zone' => true],
            ],
        ];

        $result = $this->service->evaluate($context, $rules);

        $this->assertTrue($result['special_zone']);
    }

    public function test_supports_not_in_operator(): void
    {
        $context = ['state' => 'Kano'];
        $rules = [
            [
                'conditions' => [
                    ['field' => 'state', 'operator' => 'not_in', 'value' => ['Lagos', 'Abuja', 'Rivers']],
                ],
                'actions' => ['standard_zone' => true],
            ],
        ];

        $result = $this->service->evaluate($context, $rules);

        $this->assertTrue($result['standard_zone']);
    }

    public function test_supports_equals_and_not_equals(): void
    {
        $context = ['status' => 'active'];
        $rules = [
            [
                'conditions' => [
                    ['field' => 'status', 'operator' => 'equals', 'value' => 'active'],
                ],
                'actions' => ['is_active' => true],
            ],
            [
                'conditions' => [
                    ['field' => 'status', 'operator' => 'not_equals', 'value' => 'active'],
                ],
                'actions' => ['is_inactive' => true],
            ],
        ];

        $result = $this->service->evaluate($context, $rules);

        $this->assertTrue($result['is_active']);
        $this->assertArrayNotHasKey('is_inactive', $result);
    }

    public function test_handles_dot_notation_context(): void
    {
        $context = [
            'company' => [
                'financials' => [
                    'revenue' => 80000,
                ],
            ],
        ];

        $rules = [
            [
                'conditions' => [
                    ['field' => 'company.financials.revenue', 'operator' => 'greater_than_or_equal', 'value' => 50000],
                ],
                'actions' => ['audit_required' => true],
            ],
        ];

        $result = $this->service->evaluate($context, $rules);

        $this->assertTrue($result['audit_required']);
    }

    public function test_merges_actions_from_multiple_matching_rules(): void
    {
        $context = ['revenue' => 50000, 'employees' => 20];
        $rules = [
            [
                'conditions' => [
                    ['field' => 'revenue', 'operator' => 'greater_than', 'value' => 25000],
                ],
                'actions' => ['vat_applicable' => true],
            ],
            [
                'conditions' => [
                    ['field' => 'employees', 'operator' => 'greater_than_or_equal', 'value' => 10],
                ],
                'actions' => ['paye_applicable' => true],
            ],
        ];

        $result = $this->service->evaluate($context, $rules);

        $this->assertTrue($result['vat_applicable']);
        $this->assertTrue($result['paye_applicable']);
    }

    public function test_defaults_to_all_match_when_not_specified(): void
    {
        $context = ['revenue' => 50000, 'state' => 'Abuja'];
        $rules = [
            [
                'conditions' => [
                    ['field' => 'revenue', 'operator' => 'greater_than', 'value' => 25000],
                    ['field' => 'state', 'operator' => 'equals', 'value' => 'Lagos'],
                ],
                // no 'match' key — should default to 'all'
                'actions' => ['state_tax' => true],
            ],
        ];

        $result = $this->service->evaluate($context, $rules);

        $this->assertEmpty($result);
    }
}
