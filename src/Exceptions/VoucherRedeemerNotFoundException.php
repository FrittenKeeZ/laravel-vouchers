<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Exceptions;

class VoucherRedeemerNotFoundException extends VoucherException
{
    /**
     * Exception message.
     *
     * @var string
     */
    protected $message = 'Voucher redeemer was not found with the provided entity.';

    /**
     * Exception code - we use 404 Not Found.
     *
     * @var int
     */
    protected $code = 404;
}
