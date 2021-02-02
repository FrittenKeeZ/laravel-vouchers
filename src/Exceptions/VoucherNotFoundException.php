<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Exceptions;

class VoucherNotFoundException extends VoucherException
{
    /**
     * Exception message.
     *
     * @var string
     */
    protected $message = 'Voucher was not found with the provided code.';

    /**
     * Exception code - we use 404 Not Found.
     *
     * @var int
     */
    protected $code = 404;
}
