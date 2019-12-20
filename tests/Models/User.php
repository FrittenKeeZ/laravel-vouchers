<?php

namespace FrittenKeeZ\Vouchers\Tests\Models;

use Illuminate\Notifications\Notifiable;
use FrittenKeeZ\Vouchers\Concerns\HasVouchers;
use FrittenKeeZ\Vouchers\Concerns\HasRedeemers;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;
    use HasVouchers;
    use HasRedeemers;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
}
