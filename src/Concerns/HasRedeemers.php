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
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRedeemers(): Collection
    {
        return $this->redeemers;
    }
}
