<?php

declare(strict_types=1);

namespace Modules\System\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\System\Models\Tenant;

trait BelongsToTenant
{
    /**
     * Boot the trait and apply the global scope.
     */
    public static function bootBelongsToTenant(): void
    {
        static::creating(function (Model $model) {
            if (! $model->tenant_id && app()->has('currentTenant')) {
                $model->tenant_id = app('currentTenant')->id;
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            if (app()->has('currentTenant')) {
                $builder->where(
                    $builder->qualifyColumn('tenant_id'),
                    app('currentTenant')->id
                );
            }
        });
    }

    /**
     * Get the tenant that owns the model.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
