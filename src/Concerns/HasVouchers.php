<?php

namespace FrittenKeeZ\Vouchers\Concerns;

use FrittenKeeZ\Vouchers\Config;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasVouchers
{
    /**
     * Associated voucher entities.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function voucherEntities(): MorphMany
    {
        return $this->morphMany(Config::model('entity'), 'entity');
    }

    /**
     * Get all associated vouchers.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getVouchers(): Collection
    {
        return $this->voucherEntities()->with('voucher')->get()->map->voucher;
    }
}
