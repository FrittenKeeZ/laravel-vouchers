<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Tests\Models;

use FrittenKeeZ\Vouchers\Models\Redeemer as BaseRedeemer;
use FrittenKeeZ\Vouchers\Tests\Database\Factories\RedeemerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Redeemer extends BaseRedeemer
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return RedeemerFactory::new();
    }
}
