<?php

declare(strict_types=1);

namespace Modules\RulesEngine\Services;

use Modules\RulesEngine\Enums\Operator;

class RuleEvaluatorService
{
    /**
     * Evaluate all rules against the given context and return merged actions
     * from every matching rule.
     *
     * @param  array<string, mixed>  $context  Flat or nested key-value data
     * @param  array<int, array{conditions: array<int, array{field: string, operator: string, value: mixed}>, match?: string, actions: array<string, mixed>}>  $rules
     * @return array<string, mixed> Merged actions from all matching rules
     */
    public function evaluate(array $context, array $rules): array
    {
        $result = [];

        foreach ($rules as $rule) {
            if ($this->ruleMatches($context, $rule)) {
                $result = array_merge($result, $rule['actions']);
            }
        }

        return $result;
    }

    /**
     * Evaluate rules and return actions from the first matching rule only.
     *
     * @param  array<string, mixed>  $context
     * @param  array<int, array{conditions: array<int, array{field: string, operator: string, value: mixed}>, match?: string, actions: array<string, mixed>}>  $rules
     * @return array<string, mixed>|null Actions from the first match, or null
     */
    public function evaluateFirst(array $context, array $rules): ?array
    {
        foreach ($rules as $rule) {
            if ($this->ruleMatches($context, $rule)) {
                return $rule['actions'];
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array{conditions: array<int, array{field: string, operator: string, value: mixed}>, match?: string, actions: array<string, mixed>}  $rule
     */
    private function ruleMatches(array $context, array $rule): bool
    {
        $matchMode = $rule['match'] ?? 'all';
        $conditions = $rule['conditions'];

        foreach ($conditions as $condition) {
            $fieldValue = $this->resolveField($context, $condition['field']);
            $operator = Operator::from($condition['operator']);
            $passed = $operator->evaluate($fieldValue, $condition['value']);

            if ($matchMode === 'any' && $passed) {
                return true;
            }

            if ($matchMode === 'all' && ! $passed) {
                return false;
            }
        }

        return $matchMode === 'all';
    }

    /**
     * Resolve a dot-notation field from the context array.
     */
    private function resolveField(array $context, string $field): mixed
    {
        return data_get($context, $field);
    }
}
