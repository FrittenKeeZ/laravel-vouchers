<?php

declare(strict_types=1);

use FrittenKeeZ\Vouchers\Models\Voucher;

uses(FrittenKeeZ\Vouchers\Tests\TestCase::class);

/**
 * @internal
 */

/**
 * Test Voucher::scopeCode().
 */
test('prefix and suffix helpers', function () {
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
});
