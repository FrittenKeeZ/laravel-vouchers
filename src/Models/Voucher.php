<?php

namespace FrittenKeeZ\Vouchers\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'metadata',
        'starts_at',
        'expires_at',
        'redeemed_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'metadata'    => 'array',
        'starts_at'   => 'datetime',
        'expires_at'  => 'datetime',
        'redeemed_at' => 'datetime',
    ];
}
