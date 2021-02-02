<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Facades;

use Illuminate\Support\Facades\Facade;

class Vouchers extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return 'vouchers';
    }
}
