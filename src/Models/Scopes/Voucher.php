<?php

namespace FrittenKeeZ\Vouchers\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;

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
    public function scopePrefix(Builder $query, string $prefix, string $separator = null): Builder
    {
        $clause = sprintf('%s%s%%', $prefix, is_null($separator) ? config('vouchers.separator') : $separator);

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
    public function scopeSuffix(Builder $query, string $suffix, string $separator = null): Builder
    {
        $clause = sprintf('%%%s%s', is_null($separator) ? config('vouchers.separator') : $separator, $suffix);

        return $query->where('code', 'like', $clause);
    }
}
