<?php

declare(strict_types=1);

namespace Modules\Bookkeeping\Enums;

enum LineType: string
{
    case Debit = 'debit';
    case Credit = 'credit';

    public function opposite(): self
    {
        return match ($this) {
            self::Debit => self::Credit,
            self::Credit => self::Debit,
        };
    }
}
