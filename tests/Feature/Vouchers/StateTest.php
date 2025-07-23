<?php

declare(strict_types=1);

use FrittenKeeZ\Vouchers\Tests\Models\Redeemer;
use FrittenKeeZ\Vouchers\Tests\Models\User;
use FrittenKeeZ\Vouchers\Tests\Models\Voucher;
use FrittenKeeZ\Vouchers\Vouchers;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Config::set('vouchers.models.redeemer', Redeemer::class);
    Config::set('vouchers.models.voucher', Voucher::class);
});

/**
 * Test Vouchers::redeemable() method.
 */
test('redeemable checks', function () {
    $vouchers = new Vouchers();

    // Not found.
    expect($vouchers->redeemable('NOPE'))->toBeFalse();

    // Unredeemed.
    expect($vouchers->redeemable(Voucher::factory()->create()->code))->toBeTrue();
    // Redeemed.
    expect($vouchers->redeemable(Voucher::factory()->redeemed()->create()->code))->toBeFalse();
    // Unstarted.
    expect($vouchers->redeemable(Voucher::factory()->started(false)->create()->code))->toBeFalse();
    // Expired.
    expect($vouchers->redeemable(Voucher::factory()->expired()->create()->code))->toBeFalse();

    // Callback.
    $prefixed = $vouchers->withPrefix('FOO')->create();
    expect($vouchers->redeemable($prefixed->code, fn (Voucher $voucher) => $voucher->hasPrefix('BAR')))->toBeFalse();
    expect($vouchers->redeemable($prefixed->code, fn (Voucher $voucher) => $voucher->hasPrefix('FOO')))->toBeTrue();
});

/**
 * Test Vouchers::unredeemable() method.
 */
test('unredeemable checks', function () {
    $vouchers = new Vouchers();

    // Not found.
    expect($vouchers->unredeemable('NOPE'))->toBeFalse();

    // Unredeemed.
    expect($vouchers->unredeemable(Voucher::factory()->create()->code))->toBeFalse();
    // Redeemed.
    expect(
        $vouchers->unredeemable(
            Voucher::factory()->redeemed()->has(Redeemer::factory()->for(User::factory(), 'redeemer'))->create()->code
        )
    )->toBeTrue();
    expect(
        $vouchers->unredeemable(
            Voucher::factory()->has(Redeemer::factory()->for(User::factory(), 'redeemer'))->create()->code
        )
    )->toBeTrue();
    // Unstarted.
    expect($vouchers->unredeemable(Voucher::factory()->started(false)->create()->code))->toBeFalse();
    // Expired.
    expect($vouchers->unredeemable(Voucher::factory()->expired()->create()->code))->toBeFalse();

    // Callback.
    $prefixed = Voucher::factory()
        ->has(Redeemer::factory()->for(User::factory(), 'redeemer'))
        ->create(['code' => 'FOO-CODE'])
    ;
    expect($vouchers->unredeemable($prefixed->code, fn (Voucher $voucher) => $voucher->hasPrefix('BAR')))->toBeFalse();
    expect($vouchers->unredeemable($prefixed->code, fn (Voucher $voucher) => $voucher->hasPrefix('FOO')))->toBeTrue();
});
