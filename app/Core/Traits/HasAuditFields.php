<?php

namespace App\Core\Traits;

/**
 * Trait for models that track who created/updated them.
 */
trait HasAuditFields
{
    /**
     * Boot the trait.
     */
    protected static function bootHasAuditFields(): void
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                if ($model->isFillable('created_by') && empty($model->created_by)) {
                    $model->created_by = auth()->id();
                }
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                if ($model->isFillable('updated_by')) {
                    $model->updated_by = auth()->id();
                }
            }
        });
    }
}
