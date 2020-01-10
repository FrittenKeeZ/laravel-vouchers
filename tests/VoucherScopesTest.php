<?php

namespace FrittenKeeZ\Vouchers\Tests;

use FrittenKeeZ\Vouchers\Models\Voucher;
use FrittenKeeZ\Vouchers\Facades\Vouchers;

class VoucherScopesTest extends TestCase
{
    /**
     * Test Voucher::scopeCode().
     *
     * @return void
     */
    public function testCodeScope(): void
    {
        $voucher = Vouchers::create();

        $this->assertTrue(Voucher::code($voucher->code)->exists());
        $this->assertFalse(Voucher::code('NOPE')->exists());
    }

    /**
     * Test Voucher::scopeCode().
     *
     * @return void
     */
    public function testPrefixAndSuffixScopes(): void
    {
        Voucher::create(['code' => 'FOOTESTBAR']);
        Voucher::create(['code' => 'FOOTESTBAZ']);
        Voucher::create(['code' => 'FUUTESTBAR']);
        Voucher::create(['code' => 'FUUTESTBAZ']);
        Voucher::create(['code' => 'FOO-TEST-BAR']);
        Voucher::create(['code' => 'FOO-TEST-BAZ']);
        Voucher::create(['code' => 'FUU-TEST-BAR']);
        Voucher::create(['code' => 'FUU-TEST-BAZ']);

        // Test prefix scope with separator.
        $this->assertSame(2, Voucher::prefix('FOO')->count());
        $this->assertSame(2, Voucher::prefix('FOO', '-')->count());
        $this->assertSame(2, Voucher::prefix('FUU')->count());
        $this->assertSame(2, Voucher::prefix('FUU', '-')->count());
        // Test prefix scope without separator
        $this->assertSame(4, Voucher::prefix('FOO', '')->count());
        $this->assertSame(4, Voucher::prefix('FUU', '')->count());

        // Test suffix scope with separator.
        $this->assertSame(2, Voucher::suffix('BAR')->count());
        $this->assertSame(2, Voucher::suffix('BAR', '-')->count());
        $this->assertSame(2, Voucher::suffix('BAZ')->count());
        $this->assertSame(2, Voucher::suffix('BAZ', '-')->count());
        // Test suffix scope without separator
        $this->assertSame(4, Voucher::suffix('BAR', '')->count());
        $this->assertSame(4, Voucher::suffix('BAZ', '')->count());

        // Test prefix and suffix scopes together with separator.
        $this->assertSame(1, Voucher::prefix('FOO')->suffix('BAR')->count());
        $this->assertSame(1, Voucher::prefix('FOO', '-')->suffix('BAR', '-')->count());
        $this->assertSame(1, Voucher::prefix('FUU')->suffix('BAR')->count());
        $this->assertSame(1, Voucher::prefix('FUU', '-')->suffix('BAR', '-')->count());
        $this->assertSame(1, Voucher::prefix('FOO')->suffix('BAZ')->count());
        $this->assertSame(1, Voucher::prefix('FOO', '-')->suffix('BAZ', '-')->count());
        $this->assertSame(1, Voucher::prefix('FUU')->suffix('BAZ')->count());
        $this->assertSame(1, Voucher::prefix('FUU', '-')->suffix('BAZ', '-')->count());
        // Test prefix and suffix scopes together without separator
        $this->assertSame(2, Voucher::prefix('FOO', '')->suffix('BAR', '')->count());
        $this->assertSame(2, Voucher::prefix('FUU', '')->suffix('BAR', '')->count());
        $this->assertSame(2, Voucher::prefix('FOO', '')->suffix('BAZ', '')->count());
        $this->assertSame(2, Voucher::prefix('FUU', '')->suffix('BAZ', '')->count());
    }
}
