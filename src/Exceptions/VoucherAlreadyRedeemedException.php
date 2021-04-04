<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Exceptions;

class VoucherAlreadyRedeemedException extends VoucherException
{
    /**
     * Exception message.
     *
     * @var string
     */
    protected $message = 'Voucher has already been redeemed.';

    /**
     * Exception code - we use 409 Conflict.
     *
     * @var int
     */
    protected $code = 409;
}
