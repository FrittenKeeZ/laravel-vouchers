<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Exceptions;

class VoucherStateException extends VoucherException
{
    /**
     * Exception message.
     *
     * @var string
     */
    protected $message = 'Voucher state cannot be changed.';

    /**
     * Exception code - we use 409 Conflict.
     *
     * @var int
     */
    protected $code = 409;
}
