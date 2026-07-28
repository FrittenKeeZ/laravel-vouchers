<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers;

use Illuminate\Support\Str;
use Stringable;

/**
 * Value object holding the parts of a counter based code.
 *
 * Passed to a custom code formatter, where casting to string yields the default formatting.
 */
class CodeFormat implements Stringable
{
    /**
     * Constructor.
     *
     * @param string $code      Base code without the counter.
     * @param int    $counter   Current counter value.
     * @param string $separator Separator between base code and counter.
     * @param int    $padding   Counter padding length - zero means no padding.
     * @param string $pad       Counter padding character.
     */
    public function __construct(
        public readonly string $code,
        public readonly int $counter,
        public readonly string $separator = '',
        public readonly int $padding = 0,
        public readonly string $pad = '0',
    ) {
    }

    /**
     * Get the counter, padded when a padding length is set.
     *
     * A padding length shorter than the counter is a no-op.
     */
    public function paddedCounter(): string
    {
        return Str::padLeft((string) $this->counter, $this->padding, $this->pad);
    }

    /**
     * Default formatting - base code, separator and padded counter.
     */
    public function __toString(): string
    {
        return $this->code . $this->separator . $this->paddedCounter();
    }
}
