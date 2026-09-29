<?php

namespace App\Models\Concerns;

use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::creating(function (Model $model): void {
            $tenantId = app(TenantContext::class)->id();

            if (!$model->tenant_id) {
                $model->tenant_id = $tenantId;
            }

            if ($model->tenant_id !== $tenantId) {
                throw new RuntimeException(
                    'Impossible de créer une donnée pour un autre tenant.'
                );
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder): void {
            $tenantContext = app(TenantContext::class);

            if (!$tenantContext->hasTenant()) {
                throw new RuntimeException(
                    'Aucun tenant actif pour cette requête.'
                );
            }

            $builder->where(
                $builder->getModel()->getTable() . '.tenant_id',
                $tenantContext->id()
            );
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }
}
