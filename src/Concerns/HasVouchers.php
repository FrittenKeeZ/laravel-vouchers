<?php

namespace FrittenKeeZ\Vouchers\Concerns;

use FrittenKeeZ\Vouchers\Config;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasVouchers
{
    /**
     * Associated vouchers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function vouchers(): MorphToMany
    {
        return $this->morphToMany(Config::model('voucher'), 'entity', Config::table('entities'));
    }

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
     * @deprecated Use vouchers relationship accessor instead.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getVouchers(): Collection
    {
        return $this->vouchers;
    }
}
