<?php

namespace FrittenKeeZ\Vouchers\Facades;

use Illuminate\Support\Facades\Facade;

class Vouchers extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'vouchers';
    }
}
