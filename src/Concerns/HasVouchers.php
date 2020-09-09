<?php

namespace FrittenKeeZ\Vouchers\Concerns;

use Closure;
use FrittenKeeZ\Vouchers\Config;
use FrittenKeeZ\Vouchers\Vouchers;
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
     * @codeCoverageIgnore
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getVouchers(): Collection
    {
        trigger_error(
            'getVouchers() is deprecated and will be removed in 0.2.0. ' .
            'Refactor your code to use vouchers() relationship accessor instead.',
            \E_USER_DEPRECATED
        );

        return $this->vouchers;
    }

    /**
     * Create a single voucher with this entity related.
     *
     * @param  \Closure  $callback
     * @return object
     */
    public function createVoucher(Closure $callback = null)
    {
        return $this->createVouchers(1, $callback);
    }

    /**
     * Create an amount of vouchers with this entity related.
     *
     * @param  int       $amount
     * @param  \Closure  $callback
     * @return object|array
     */
    public function createVouchers(int $amount, Closure $callback = null)
    {
        $vouchers = new Vouchers();

        if ($callback) {
            $callback($vouchers);
        }

        // Prepend owner to entities.
        $entities = $vouchers->getEntities();
        array_unshift($entities, $this);

        return $vouchers->withEntities(...$entities)->create($amount);
    }
}
