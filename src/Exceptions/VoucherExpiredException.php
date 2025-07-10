<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Exceptions;

class VoucherExpiredException extends VoucherNotRedeemableException
{
    /**
     * Exception message.
     *
     * @var string
     */
    protected $message = 'Voucher is expired.';
}
