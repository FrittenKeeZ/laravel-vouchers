<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Tests;

use FrittenKeeZ\Vouchers\Models\Voucher;

/**
 * @internal
 */
class VoucherHelpersTest extends TestCase
{
    /**
     * Test Voucher::scopeCode().
     *
     * @return void
     */
    public function testPrefixAndSuffixHelpers(): void
    {
        $unseparated = Voucher::make(['code' => 'FOOTESTBAR']);
        $separated = Voucher::make(['code' => 'FOO-TEST-BAR']);

        $this->assertTrue($separated->hasPrefix('FOO'));
        $this->assertFalse($unseparated->hasPrefix('FOO'));
        $this->assertTrue($separated->hasPrefix('FOO', '-'));
        $this->assertFalse($unseparated->hasPrefix('FOO', '-'));
        $this->assertTrue($separated->hasPrefix('FOO', ''));
        $this->assertTrue($unseparated->hasPrefix('FOO', ''));
        $this->assertFalse($separated->hasPrefix('FUU', ''));
        $this->assertFalse($unseparated->hasPrefix('FUU', ''));
        $this->assertTrue($separated->hasSuffix('BAR'));
        $this->assertFalse($unseparated->hasSuffix('BAR'));
        $this->assertTrue($separated->hasSuffix('BAR', '-'));
        $this->assertFalse($unseparated->hasSuffix('BAR', '-'));
        $this->assertTrue($separated->hasSuffix('BAR', ''));
        $this->assertTrue($unseparated->hasSuffix('BAR', ''));
        $this->assertFalse($separated->hasSuffix('BAZ', ''));
        $this->assertFalse($unseparated->hasSuffix('BAZ', ''));
    }
}
