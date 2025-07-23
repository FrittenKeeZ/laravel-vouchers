<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Exceptions;

class VoucherUnstartedException extends VoucherStateException
{
    /**
     * Exception message.
     *
     * @var string
     */
    protected $message = 'Voucher is not yet started.';
}
