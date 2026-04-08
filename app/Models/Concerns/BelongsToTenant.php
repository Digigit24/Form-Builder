<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Scopes a model to the currently authenticated user's tenant.
 *
 * Any query against a model using this trait will automatically be filtered
 * by the authenticated user's tenant_id, so admins can never see another
 * tenant's data. Public (unauthenticated) requests bypass the scope, which
 * is what we want for the public form renderer.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $builder->where($builder->getModel()->getTable().'.tenant_id', auth()->user()->tenant_id);
            }
        });

        static::creating(function ($model) {
            if (! $model->tenant_id && auth()->check()) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
