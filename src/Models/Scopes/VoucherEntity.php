<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

trait VoucherEntity
{
    /**
     * Scope voucher query to specific entity type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string                                 $type
     * @return \Illuminate\Database\Eloquent\Buildexr
     */
    public function scopeWithEntityType(Builder $query, string $type): Builder
    {
        $class = Relation::getMorphedModel($type) ?? $type;

        return $query->where('entity_type', '=', (new $class)->getMorphClass());
    }

    /**
     * Scope voucher query to specific entity.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model    $entity
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithEntity(Builder $query, Model $entity): Builder
    {
        return $query->withEntityType(\get_class($entity))->where('entity_id', '=', $entity->getKey());
    }
}
