<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Carbon;

trait Voucher
{
    /**
     * Scope voucher query to a specific code.
     */
    public function scopeCode(Builder $query, string $code): Builder
    {
        return $query->where($this->getTable() . '.code', '=', $code);
    }

    /**
     * Scope voucher query to (or exclude) a specific prefix, optionally specifying a separator different from config.
     */
    public function scopeWithPrefix(
        Builder $query,
        string $prefix,
        ?string $separator = null,
        bool $not = false
    ): Builder {
        $clause = sprintf('%s%s%%', $prefix, $separator === null ? config('vouchers.separator') : $separator);

        return $query->where($this->getTable() . '.code', $not ? 'not like' : 'like', $clause);
    }

    /**
     * Scope voucher query to exclude a specific prefix, optionally specifying a separator different from config.
     */
    public function scopeWithoutPrefix(Builder $query, string $prefix, ?string $separator = null): Builder
    {
        return $this->scopeWithPrefix($query, $prefix, $separator, true);
    }

    /**
     * Scope voucher query to (or exclude) a specific suffix, optionally specifying a separator different from config.
     */
    public function scopeWithSuffix(
        Builder $query,
        string $suffix,
        ?string $separator = null,
        bool $not = false
    ): Builder {
        $clause = sprintf('%%%s%s', $separator === null ? config('vouchers.separator') : $separator, $suffix);

        return $query->where($this->getTable() . '.code', $not ? 'not like' : 'like', $clause);
    }

    /**
     * Scope voucher query to exclude a specific suffix, optionally specifying a separator different from config.
     */
    public function scopeWithoutSuffix(Builder $query, string $suffix, ?string $separator = null): Builder
    {
        return $this->scopeWithSuffix($query, $suffix, $separator, true);
    }

    /**
     * Scope voucher query to started or unstarted vouchers.
     */
    public function scopeWithStarted(Builder $query, bool $started = true): Builder
    {
        $column = $this->getTable() . '.starts_at';

        if ($started) {
            return $query->where(
                fn (Builder $query) => $query->whereNull($column)->orWhere($column, '<=', Carbon::now())
            );
        }

        return $query->where($column, '>', Carbon::now());
    }

    /**
     * Scope voucher query to unstarted vouchers.
     */
    public function scopeWithoutStarted(Builder $query): Builder
    {
        return $this->scopeWithStarted($query, false);
    }

    /**
     * Scope voucher query to expired or unexpired vouchers.
     */
    public function scopeWithExpired(Builder $query, bool $expired = true): Builder
    {
        $column = $this->getTable() . '.expires_at';

        return $query->where(fn (Builder $query) => $expired
            ? $query->whereNotNull($column)->where($column, '<=', Carbon::now())
            : $query->whereNull($column)->orWhere($column, '>', Carbon::now())
        );
    }

    /**
     * Scope voucher query to unexpired vouchers.
     */
    public function scopeWithoutExpired(Builder $query): Builder
    {
        return $this->scopeWithExpired($query, false);
    }

    /**
     * Scope voucher query to redeemed or without redeemed vouchers.
     */
    public function scopeWithRedeemed(Builder $query, bool $redeemed = true): Builder
    {
        $column = $this->getTable() . '.redeemed_at';

        return $redeemed ? $query->whereNotNull($column) : $query->whereNull($column);
    }

    /**
     * Scope voucher query to without redeemed vouchers.
     */
    public function scopeWithoutRedeemed(Builder $query): Builder
    {
        return $this->scopeWithRedeemed($query, false);
    }

    /**
     * Scope voucher query to redeemable or without redeemable vouchers.
     */
    public function scopeWithRedeemable(Builder $query, bool $redeemable = true): Builder
    {
        if ($redeemable) {
            return $query->withRedeemed(false)->withStarted(true)->withExpired(false);
        }

        return $query
            ->where(fn (Builder $query) => $query->withRedeemed(true))
            ->orWhere(fn (Builder $query) => $query->withStarted(false))
            ->orWhere(fn (Builder $query) => $query->withExpired(true))
        ;
    }

    /**
     * Scope voucher query to without redeemable vouchers.
     */
    public function scopeWithoutRedeemable(Builder $query): Builder
    {
        return $this->scopeWithRedeemable($query, false);
    }

    /**
     * Scope voucher query to unredeemable or without unredeemable vouchers.
     */
    public function scopeWithUnredeemable(Builder $query, bool $unredeemable = true): Builder
    {
        if ($unredeemable) {
            return $query->has('redeemers')->withStarted(true)->withExpired(false);
        }

        return $query
            ->where(fn (Builder $query) => $query->doesntHave('redeemers'))
            ->orWhere(fn (Builder $query) => $query->withStarted(false))
            ->orWhere(fn (Builder $query) => $query->withExpired(true))
        ;
    }

    /**
     * Scope voucher query to without unredeemable vouchers.
     */
    public function scopeWithoutUnredeemable(Builder $query): Builder
    {
        return $this->scopeWithUnredeemable($query, false);
    }

    /**
     * Scope voucher query to have voucher entities, optionally of a specific type (class or alias).
     */
    public function scopeWithEntities(Builder $query, ?string $type = null): Builder
    {
        return empty($type)
            ? $query->has('voucherEntities')
            : $query->whereHas('voucherEntities', fn (Builder $query) => $query->withEntityType($type));
    }

    /**
     * Scope voucher query to specific owner type (class or alias).
     */
    public function scopeWithOwnerType(Builder $query, string $type): Builder
    {
        $class = Relation::getMorphedModel($type) ?? $type;

        return $query->where($this->getTable() . '.owner_type', '=', (new $class())->getMorphClass());
    }

    /**
     * Scope voucher query to specific owner.
     */
    public function scopeWithOwner(Builder $query, Model $owner): Builder
    {
        return $query
            ->withOwnerType(\get_class($owner))
            ->where($this->getTable() . '.owner_id', '=', $owner->getKey())
        ;
    }

    /**
     * Scope voucher query to no owners.
     */
    public function scopeWithoutOwner(Builder $query): Builder
    {
        return $query->whereNull($this->getTable() . '.owner_type')->whereNull($this->getTable() . '.owner_id');
    }
}
