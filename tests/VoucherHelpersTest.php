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

    expect($separated->hasPrefix('FOO'))->toBeTrue();
    expect($unseparated->hasPrefix('FOO'))->toBeFalse();
    expect($separated->hasPrefix('FOO', '-'))->toBeTrue();
    expect($unseparated->hasPrefix('FOO', '-'))->toBeFalse();
    expect($separated->hasPrefix('FOO', ''))->toBeTrue();
    expect($unseparated->hasPrefix('FOO', ''))->toBeTrue();
    expect($separated->hasPrefix('FUU', ''))->toBeFalse();
    expect($unseparated->hasPrefix('FUU', ''))->toBeFalse();
    expect($separated->hasSuffix('BAR'))->toBeTrue();
    expect($unseparated->hasSuffix('BAR'))->toBeFalse();
    expect($separated->hasSuffix('BAR', '-'))->toBeTrue();
    expect($unseparated->hasSuffix('BAR', '-'))->toBeFalse();
    expect($separated->hasSuffix('BAR', ''))->toBeTrue();
    expect($unseparated->hasSuffix('BAR', ''))->toBeTrue();
    expect($separated->hasSuffix('BAZ', ''))->toBeFalse();
    expect($unseparated->hasSuffix('BAZ', ''))->toBeFalse();
});
