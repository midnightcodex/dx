<?php

namespace App\Core\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait for models that belong to an organization (multi-tenancy).
 */
trait BelongsToOrganization
{
    /**
     * Boot the trait to add global scope.
     */
    protected static function bootBelongsToOrganization(): void
    {
        // Auto-filter all model queries by current user's organization
        static::addGlobalScope('organization', function (Builder $builder) {
            if (!auth()->check()) {
                return;
            }

            $orgId = auth()->user()->organization_id;
            if (!$orgId) {
                return;
            }

            $builder->where($builder->getModel()->getTable() . '.organization_id', $orgId);
        });

        // Auto-set organization_id on creation
        static::creating(function ($model) {
            if (empty($model->organization_id) && auth()->check()) {
                $model->organization_id = auth()->user()->organization_id;
            }
        });
    }

    /**
     * Scope a query to only include records for the current organization.
     */
    public function scopeForOrganization($query, ?string $organizationId = null)
    {
        $orgId = $organizationId ?? (auth()->check() ? auth()->user()->organization_id : null);

        if ($orgId) {
            return $query->where('organization_id', $orgId);
        }

        return $query;
    }
}
