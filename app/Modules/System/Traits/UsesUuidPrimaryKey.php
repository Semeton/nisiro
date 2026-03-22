<?php

declare(strict_types=1);

namespace Modules\System\Traits;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

trait UsesUuidPrimaryKey
{
    use HasUuids;

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Get the type of the primary key ID.
     */
    public function getKeyType(): string
    {
        return 'string';
    }
}
