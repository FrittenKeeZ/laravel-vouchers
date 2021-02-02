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
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string                                 $code
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCode(Builder $query, string $code): Builder
    {
        return $query->where('code', '=', $code);
    }

    /**
     * Scope voucher query to a specific prefix, optionally specifying a separator different from config.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string                                 $prefix
     * @param  string|null                            $separator
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithPrefix(Builder $query, string $prefix, string $separator = null): Builder
    {
        $clause = sprintf('%s%s%%', $prefix, \is_null($separator) ? config('vouchers.separator') : $separator);

        return $query->where('code', 'like', $clause);
    }

    /**
     * Scope voucher query to a specific suffix, optionally specifying a separator different from config.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string                                 $suffix
     * @param  string|null                            $separator
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithSuffix(Builder $query, string $suffix, string $separator = null): Builder
    {
        $clause = sprintf('%%%s%s', \is_null($separator) ? config('vouchers.separator') : $separator, $suffix);

        return $query->where('code', 'like', $clause);
    }

    /**
     * Scope voucher query to started or unstarted vouchers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  bool                                   $started
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStarted(Builder $query, bool $started = true): Builder
    {
        if ($started) {
            return $query->where(function (Builder $query) {
                return $query->whereNull('starts_at')->orWhere('starts_at', '<=', Carbon::now());
            });
        }

        return $query->where('starts_at', '>', Carbon::now());
    }

    /**
     * Scope voucher query to expired or unexpired vouchers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  bool                                   $expired
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithExpired(Builder $query, bool $expired = true): Builder
    {
        return $query->where(function (Builder $query) use ($expired) {
            return $expired
                ? $query->whereNotNull('expires_at')->where('expires_at', '<=', Carbon::now())
                : $query->whereNull('expires_at')->orWhere('expires_at', '>', Carbon::now());
        });
    }

    /**
     * Scope voucher query to redeemed or unredeemed vouchers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  bool                                   $redeemed
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithRedeemed(Builder $query, bool $redeemed = true): Builder
    {
        return $redeemed ? $query->whereNotNull('redeemed_at') : $query->whereNull('redeemed_at');
    }

    /**
     * Scope voucher query to redeemable or unredeemable vouchers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  bool                                   $redeemable
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithRedeemable(Builder $query, bool $redeemable = true): Builder
    {
        if ($redeemable) {
            return $query->withRedeemed(false)->withStarted(true)->withExpired(false);
        }

        return $query
            ->where(function (Builder $query) {
                return $query->withRedeemed(true);
            })->orWhere(function (Builder $query) {
                return $query->withStarted(false);
            })->orWhere(function (Builder $query) {
                return $query->withExpired(true);
            });
    }

    /**
     * Scope voucher query to specific owner.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model    $owner
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithOwner(Builder $query, Model $owner): Builder
    {
        $class = \get_class($owner);
        $alias = array_flip(Relation::morphMap())[$class] ?? $class;

        return $query->where('owner_id', '=', $owner->getKey())->where('owner_type', '=', $alias);
    }
}
