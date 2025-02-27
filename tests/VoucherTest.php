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
        $this->assertFalse($voucher->isRedeemed());

        return false;
    });

    $vouchers = new Vouchers();
    $user = User::factory()->create();
    $voucher = $vouchers->create();
    $this->assertTrue($voucher->isRedeemable());
    $this->assertFalse($vouchers->redeem($voucher->code, $user));
    $voucher->refresh();
    $this->assertFalse($voucher->isRedeemed());
    $this->assertEmpty($voucher->redeemers);
});

/**
 * Test redeemed event.
 */
test('redeemed event', function () {
    // Check that voucher is redeemed.
    Voucher::redeemed(function (Voucher $voucher) {
        $this->assertTrue($voucher->isRedeemed());
    });

    $vouchers = new Vouchers();
    $user = User::factory()->create();
    $voucher = $vouchers->create();
    $this->assertTrue($voucher->isRedeemable());
    $this->assertTrue($vouchers->redeem($voucher->code, $user));
    $voucher->refresh();
    $this->assertNotEmpty($voucher->redeemers);
    $this->assertFalse($voucher->redeem($voucher->redeemers->first()));
});

/**
 * Test shouldMarkRedeemed event.
 */
test('should mark redeemed event', function () {
    // Don't mark voucher as redeemed for multiple uses.
    Voucher::shouldMarkRedeemed(function (Voucher $voucher) {
        $this->assertFalse($voucher->isRedeemed());

        return false;
    });

    $vouchers = new Vouchers();
    $user = User::factory()->create();
    $voucher = $vouchers->create();
    $this->assertTrue($voucher->isRedeemable());
    $this->assertTrue($vouchers->redeem($voucher->code, $user));
    $voucher->refresh();
    $this->assertFalse($voucher->isRedeemed());
    $this->assertNotEmpty($voucher->redeemers);
    $this->assertTrue($voucher->redeem($voucher->redeemers->first()));
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
    $this->assertTrue($voucher->isRedeemable());
    $this->assertFalse($vouchers->redeem($voucher->code, $other));
    $this->assertFalse($voucher->isRedeemed());
    $this->assertEmpty($voucher->redeemers);
    $voucher->refresh();
    $this->assertTrue($voucher->isRedeemable());
    $this->assertTrue($vouchers->redeem($voucher->code, $user));
    $voucher->refresh();
    $this->assertTrue($voucher->isRedeemed());
    $this->assertNotEmpty($voucher->redeemers);
});
