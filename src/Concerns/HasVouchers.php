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
     */
    public function vouchers(): MorphMany
    {
        return $this->morphMany(Config::model('voucher'), 'owner');
    }

    /**
     * Associated vouchers.
     */
    public function associatedVouchers(): MorphToMany
    {
        return $this->morphToMany(Config::model('voucher'), 'entity', Config::table('entities'));
    }

    /**
     * Associated voucher entities.
     */
    public function voucherEntities(): MorphMany
    {
        return $this->morphMany(Config::model('entity'), 'entity');
    }

    /**
     * Create a single voucher with this entity related.
     */
    public function createVoucher(?Closure $callback = null): object
    {
        return $this->createVouchers(1, $callback);
    }

    /**
     * Create an amount of vouchers with this entity related.
     */
    public function createVouchers(int $amount, ?Closure $callback = null): array|object
    {
        $vouchers = new Vouchers;

        if ($callback) {
            $callback($vouchers);
        }

        return $vouchers->withOwner($this)->create($amount);
    }
}
