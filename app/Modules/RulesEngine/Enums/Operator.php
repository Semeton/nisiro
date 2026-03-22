<?php

declare(strict_types=1);

namespace Modules\RulesEngine\Enums;

enum Operator: string
{
    case Equals = 'equals';
    case NotEquals = 'not_equals';
    case GreaterThan = 'greater_than';
    case GreaterThanOrEqual = 'greater_than_or_equal';
    case LessThan = 'less_than';
    case LessThanOrEqual = 'less_than_or_equal';
    case In = 'in';
    case NotIn = 'not_in';

    public function evaluate(mixed $left, mixed $right): bool
    {
        return match ($this) {
            self::Equals => $left == $right,
            self::NotEquals => $left != $right,
            self::GreaterThan => $left > $right,
            self::GreaterThanOrEqual => $left >= $right,
            self::LessThan => $left < $right,
            self::LessThanOrEqual => $left <= $right,
            self::In => is_array($right) && in_array($left, $right, false),
            self::NotIn => is_array($right) && ! in_array($left, $right, false),
        };
    }
}
