<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

trait VoucherEntity
{
    /**
     * Scope voucher query to specific entity type (class or alias).
     */
    public function scopeWithEntityType(Builder $query, string $type): Builder
    {
        $class = Relation::getMorphedModel($type) ?? $type;

        return $query->where($this->getTable() . '.entity_type', '=', (new $class())->getMorphClass());
    }

    /**
     * Scope voucher query to specific entity.
     */
    public function scopeWithEntity(Builder $query, Model $entity): Builder
    {
        return $query
            ->withEntityType(\get_class($entity))
            ->where($this->getTable() . '.entity_id', '=', $entity->getKey())
        ;
    }
}
