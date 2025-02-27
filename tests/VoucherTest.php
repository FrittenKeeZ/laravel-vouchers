<?php

declare(strict_types=1);

use FrittenKeeZ\Vouchers\Models\Voucher;
use FrittenKeeZ\Vouchers\Tests\Models\User;
use FrittenKeeZ\Vouchers\Vouchers;

uses(FrittenKeeZ\Vouchers\Tests\TestCase::class);

/**
 * @internal
 */

/**
 * Test redeeming event.
 */
test('redeeming event', function () {
    // Prevent redeeming.
    Voucher::redeeming(function (Voucher $voucher) {
        expect($voucher->isRedeemed())->toBeFalse();

        return false;
    });

    $vouchers = new Vouchers();
    $user = User::factory()->create();
    $voucher = $vouchers->create();
    expect($voucher->isRedeemable())->toBeTrue();
    expect($vouchers->redeem($voucher->code, $user))->toBeFalse();
    $voucher->refresh();
    expect($voucher->isRedeemed())->toBeFalse();
    expect($voucher->redeemers)->toBeEmpty();
});

/**
 * Test redeemed event.
 */
test('redeemed event', function () {
    // Check that voucher is redeemed.
    Voucher::redeemed(function (Voucher $voucher) {
        expect($voucher->isRedeemed())->toBeTrue();
    });

    $vouchers = new Vouchers();
    $user = User::factory()->create();
    $voucher = $vouchers->create();
    expect($voucher->isRedeemable())->toBeTrue();
    expect($vouchers->redeem($voucher->code, $user))->toBeTrue();
    $voucher->refresh();
    $this->assertNotEmpty($voucher->redeemers);
    expect($voucher->redeem($voucher->redeemers->first()))->toBeFalse();
});

/**
 * Test shouldMarkRedeemed event.
 */
test('should mark redeemed event', function () {
    // Don't mark voucher as redeemed for multiple uses.
    Voucher::shouldMarkRedeemed(function (Voucher $voucher) {
        expect($voucher->isRedeemed())->toBeFalse();

        return false;
    });

    $vouchers = new Vouchers();
    $user = User::factory()->create();
    $voucher = $vouchers->create();
    expect($voucher->isRedeemable())->toBeTrue();
    expect($vouchers->redeem($voucher->code, $user))->toBeTrue();
    $voucher->refresh();
    expect($voucher->isRedeemed())->toBeFalse();
    $this->assertNotEmpty($voucher->redeemers);
    expect($voucher->redeem($voucher->redeemers->first()))->toBeTrue();
});

/**
 * Test redeeming by owning user only event.
 */
test('redeeming by owning user only event', function () {
    // Allow redeeming only for related user.
    Voucher::redeeming(function (Voucher $voucher) {
        return $voucher->redeemer->redeemer->is($voucher->owner);
    });

    $vouchers = new Vouchers();
    $user = User::factory()->create();
    $other = User::factory()->create();
    $voucher = $vouchers->withOwner($user)->create();
    expect($voucher->isRedeemable())->toBeTrue();
    expect($vouchers->redeem($voucher->code, $other))->toBeFalse();
    expect($voucher->isRedeemed())->toBeFalse();
    expect($voucher->redeemers)->toBeEmpty();
    $voucher->refresh();
    expect($voucher->isRedeemable())->toBeTrue();
    expect($vouchers->redeem($voucher->code, $user))->toBeTrue();
    $voucher->refresh();
    expect($voucher->isRedeemed())->toBeTrue();
    $this->assertNotEmpty($voucher->redeemers);
});
