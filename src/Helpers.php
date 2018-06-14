<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers;

use FrittenKeeZ\Vouchers\Models\Voucher;

class Helpers
{
    /**
     * Get model class name from config.
     *
     * @param  string  $name
     * @return string|null
     */
    public static function model(string $name): ?string
    {
        return config('vouchers.models.' . $name);
    }

    /**
     * Get database table name for a model from config.
     *
     * @param  string  $name
     * @return string|null
     */
    public static function table(string $name): ?string
    {
        return config('vouchers.tables.' . $name);
    }
}
