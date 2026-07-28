<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Exceptions;

class CounterException extends VoucherException
{
    /**
     * Exception message.
     *
     * @var string
     */
    protected $message = 'Counter options are only supported for static codes without mask asterisks.';

    /**
     * Exception code - we use 400 Bad Request.
     *
     * @var int
     */
    protected $code = 400;
}
