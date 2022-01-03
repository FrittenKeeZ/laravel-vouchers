<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Concerns;

use Closure;
use FrittenKeeZ\Vouchers\Config;
use FrittenKeeZ\Vouchers\Vouchers;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasVouchers
{
    /**
     * Owned vouchers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function vouchers(): MorphMany
    {
        return $this->morphMany(Config::model('voucher'), 'owner');
    }

    /**
     * Associated vouchers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function associatedVouchers(): MorphToMany
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
     * Create a single voucher with this entity related.
     *
     * @param \Closure|null $callback
     *
     * @return object
     */
    public function createVoucher(?Closure $callback = null): object
    {
        return $this->createVouchers(1, $callback);
    }

    /**
     * Create an amount of vouchers with this entity related.
     *
     * @param int           $amount
     * @param \Closure|null $callback
     *
     * @return object|array
     */
    public function createVouchers(int $amount, ?Closure $callback = null)
    {
        $vouchers = new Vouchers();

        if ($callback) {
            $callback($vouchers);
        }

        return $vouchers->withOwner($this)->create($amount);
    }
}
