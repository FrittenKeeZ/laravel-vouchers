<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Exceptions;

class VoucherRedeemedException extends VoucherNotRedeemableException
{
    /**
     * Exception message.
     *
     * @var string
     */
    protected $message = 'Voucher has already been redeemed.';
}
