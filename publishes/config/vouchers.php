<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Code Generation
    |--------------------------------------------------------------------------
    */

    /*
     * List of characters allowed in code generation.
     * To avoid confusion the characters 1 (one), 0 (zero), I and O are removed.
     */
    'characters' => '23456789ABCDEFGHJKLMNPQRSTUVWXYZ',

    /*
     * Code generation mask.
     * Only asterisks will be replaced, so you can add or remove as many asterisks you want.
     *
     * Ex: ***-**-***
     */
    'mask' => '****-****',

    /*
     * Code prefix.
     * If defined all codes will start with this string.
     *
     * Ex. FOO-1234-5678
     */
    'prefix' => null,

    /*
     * Code suffix.
     * If defined all codes will end with this string.
     *
     * Ex. 1234-5678-BAR
     */
    'suffix' => null,

    /*
     * Separator for code prefix and suffix.
     */
    'separator' => '-',

    /*
    |--------------------------------------------------------------------------
    | Database
    |--------------------------------------------------------------------------
    */

    'models' => [
        'entity'   => FrittenKeeZ\Vouchers\Models\VoucherEntity::class,
        'redeemer' => FrittenKeeZ\Vouchers\Models\Redeemer::class,
        'voucher'  => FrittenKeeZ\Vouchers\Models\Voucher::class,
    ],

    'tables' => [
        'entities'  => 'voucher_entity',
        'redeemers' => 'redeemers',
        'vouchers'  => 'vouchers',
    ],
];
