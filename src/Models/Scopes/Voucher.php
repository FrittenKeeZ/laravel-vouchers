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
}
