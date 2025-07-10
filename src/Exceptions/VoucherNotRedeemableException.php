<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Exceptions;

class VoucherNotRedeemableException extends VoucherException
{
    /**
     * Exception message.
     *
     * @var string
     */
    protected $message = 'Voucher is not redeemable.';

    /**
     * Exception code - we use 409 Conflict.
     *
     * @var int
     */
    protected $code = 409;
}
