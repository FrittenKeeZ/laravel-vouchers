<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Concerns;

use FrittenKeeZ\Vouchers\Config;
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
}
