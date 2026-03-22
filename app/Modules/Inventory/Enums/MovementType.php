<?php

declare(strict_types=1);

namespace Modules\Inventory\Enums;

enum MovementType: string
{
    case Purchase = 'purchase';
    case Sale = 'sale';
    case Adjustment = 'adjustment';
}
