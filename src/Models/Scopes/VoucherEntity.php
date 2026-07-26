<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Models\Scopes;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

trait VoucherEntity
{
    /**
     * Scope voucher query to specific entity type (class or alias).
     */
    #[Scope]
    protected function withEntityType(Builder $query, string $type): Builder
    {
        $class = Relation::getMorphedModel($type) ?? $type;

        return $query->where($this->getTable() . '.entity_type', '=', Relation::getMorphAlias($class));
    }

    /**
     * Scope voucher query to specific entity.
     */
    #[Scope]
    protected function withEntity(Builder $query, Model $entity): Builder
    {
        return $query
            ->withEntityType(\get_class($entity))
            ->where($this->getTable() . '.entity_id', '=', $entity->getKey())
        ;
    }
}
