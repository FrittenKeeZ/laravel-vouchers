<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Exceptions;

class InfiniteLoopException extends VoucherException
{
    /**
     * Exception message.
     *
     * @var string
     */
    protected $message = 'Infinite loop detected while generating voucher codes.';

    /**
     * Exception code - we use 508 Loop Detected.
     *
     * @var int
     */
    protected $code = 508;
}
