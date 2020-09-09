<?php

namespace FrittenKeeZ\Vouchers\Concerns;

use FrittenKeeZ\Vouchers\Config;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasRedeemers
{
    /**
     * Associated redeemers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function redeemers(): MorphMany
    {
        return $this->morphMany(Config::model('redeemer'), 'redeemer');
    }

    /**
     * Get all associated redeemers.
     *
     * @deprecated Use redeemers relationship accessor instead.
     * @codeCoverageIgnore
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRedeemers(): Collection
    {
        trigger_error(
            'getRedeemers() is deprecated and will be removed in 0.2.0. ' .
            'Refactor your code to use redeemers() relationship accessor instead.',
            \E_USER_DEPRECATED
        );

        return $this->redeemers;
    }
}
