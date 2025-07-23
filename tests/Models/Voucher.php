<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Tests\Models;

use FrittenKeeZ\Vouchers\Models\Voucher as BaseVoucher;
use FrittenKeeZ\Vouchers\Tests\Database\Factories\VoucherFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Voucher extends BaseVoucher
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return VoucherFactory::new();
    }
}
